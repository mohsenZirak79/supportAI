<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Auth\Requests\LoginOtpRequest;
use App\Domains\Auth\Requests\LoginRequest;
use App\Domains\Auth\Requests\RegisterRequest;
use App\Domains\Shared\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use function Symfony\Component\String\u;

class AuthController
{
    public function register(RegisterRequest $request)
    {

        //TODO on comment this line
        /*if (RateLimiter::tooManyAttempts('register_' . $request->ip(), 5)) {
            return response()->json(['error' => ['code' => 'TOO_MANY_ATTEMPTS', 'message' => 'Too many registration attempts']], 429);
        }
        RateLimiter::hit('register_' . $request->ip(), 60);*/
        $user = User::create([
            'name' => $request->name,
            'family' => $request->family,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'national_id' => $request->national_id,
            'postal_code' => $request->postal_code,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
        ]);

        $role = \Spatie\Permission\Models\Role::find(3);
        if ($role) {
            $user->assignRole($role);
        }


        $otp = rand(100000, 999999);
        Cache::put('otp_register_' . $request->phone, $otp, 120); // 2 دقیقه
        $user->otp_sent_at = now();
        $user->save();

        return redirect()->route('login')->with('success', 'ثبت‌نام با موفقیت انجام شد. لطفاً وارد شوید.');


//        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
//        $twilio->messages->create($request->phone, ['from' => env('TWILIO_PHONE'), 'body' => "Activation OTP: $otp"]);
//
//        if (env('APP_ENV') === 'local') {
//            return ['message' => 'OTP sent (for test: ' . $otp . ')'];
//        }
//        return ['message' => 'OTP sent for activation'];

//        dump($request->all());
//        $user = User::create([
//            'name' => $request->name,
//            'family' => $request->family,
//            'phone' => $request->phone,
//            'national_id' => $request->national_id,
//            'birth_date' => $request->birth_date,
//            'address' => $request->address,
//            'postal_code' => $request->postal_code,
//            'email' => $request->email,
//            'password' => Hash::make($request->password),
//            // other fields
//        ]);
//        // Send OTP if 2FA
//        $otp = rand(100000, 999999);
//        Cache::put('otp_register_' . $request->phone, $otp, 300);
//        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
//        $twilio->messages->create($request->phone, ['from' => env('TWILIO_PHONE'), 'body' => "Activation OTP: $otp"]);
//        return ['message' => 'User created, verify OTP to activate'];
    }

    public function otpRequest(OtpRequest $request)
    {
        // Generate/send OTP (use Twilio or email)
    }

    public function otpVerify(Request $request)
    {
        // Verify OTP, issue tokens
        $token = $request->user()->createToken('api', ['user:read']); // Scopes
        return [
            'access_token' => $token->plainTextToken,
            'refresh_token' => $token->plainTextToken // Simulate refresh
        ];
    }

//    public function login(LoginRequest $request)
//    {
//        $phone = $request->phone;
//        $user = User::where('phone', $phone)->first();
//
//        if (!$user) {
//            return response()->json(['error' => ['code' => 'USER_NOT_FOUND', 'message' => 'User not found']], 404);
//        }
//
//        // روش ۲: اگر پسورد وارد شده، چک کن
//        if ($request->password && Hash::check($request->password, $user->password)) {
//            $token = $user->createToken('api');
//            return ['access_token' => $token->plainTextToken];
//        }
//
//        // روش ۱: اگر پسورد وارد نشده، OTP ارسال کن
//        if (!$request->password) {
//            $otp = rand(100000, 999999);
//            Cache::put('otp_login_' . $phone, $otp, 300);
//            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
//            $twilio->messages->create($phone, ['from' => env('TWILIO_PHONE'), 'body' => "Login OTP: $otp"]);
//            return ['message' => 'OTP sent for login'];
//        }
//
//        return response()->json(['error' => ['code' => 'AUTH_ERROR', 'message' => 'Invalid credentials']], 401);
//    }

//    public function logout(Request $request)
//    {
//        $request->user()->currentAccessToken()->delete();
//        return ['message' => 'Logged out'];
//    }

    public function sessions(Request $request)
    {
        return $request->user()->tokens;
    }

    public function deleteSession($id)
    {
        auth()->user()->tokens()->where('id', $id)->delete();
    }

    public function twoFaEnable(Request $request)
    {
        // Enable 2FA, generate secret
    }

    public function login(Request $request)
    {
        // 1. ولیدیشن
        $request->validate([
            'phone' => ['required', 'regex:/^(\+98|0)?9\d{9}$/', 'exists:users,phone'],
        ]);

        // 2. جلوگیری از حملات brute-force
        $rateKey = 'login_' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            return response()->json([
                'error' => [
                    'code' => 'TOO_MANY_ATTEMPTS',
                    'message' => 'Too many login attempts'
                ]
            ], 429);
        }
        RateLimiter::hit($rateKey, 60);

        // 3. پیدا کردن یوزر
        $user = User::where('phone', $request->phone)->first();

        // 4. ساخت OTP
        $otp = rand(100000, 999999);
        Cache::put("otp_login_{$request->phone}", $otp, now()->addMinutes(2));

        $user->otp_sent_at = now();
        $user->save();

        // 5. ارسال OTP با Twilio
        /*$twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $twilio->messages->create($request->phone, [
            'from' => env('TWILIO_PHONE'),
            'body' => "Login OTP: $otp"
        ]);*/

        // 6. محیط local → برگرداندن OTP برای تست
        if (app()->environment('local')) {
            return response()->json([
                'message' => 'OTP sent',
                'otp' => $otp
            ]);
        }

        return response()->json(['message' => 'OTP sent for login']);
    }

//    public function verifyLoginOtp(LoginOtpRequest $request)
//    {
//        if (Cache::get('otp_login_' . $request->phone) == $request->otp) {
//            $user = User::where('phone', $request->phone)->first();
//            $token = $user->createToken('api');
//            return ['access_token' => $token->plainTextToken];
//        }
//        return response()->json(['error' => ['code' => 'OTP_INVALID', 'message' => 'Invalid OTP']], 400);
//    }

    public function verifyLoginOtp(LoginOtpRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        // 1️⃣ چک OTP و زمان
        if (!$user || now()->diffInSeconds($user->otp_sent_at) > 120) {
            return response()->json([
                'error' => [
                    'code' => 'OTP_EXPIRED',
                    'message' => 'OTP expired'
                ]
            ], 400);
        }

        if (Cache::get('otp_login_' . $request->phone) != $request->otp) {
            return response()->json([
                'error' => [
                    'code' => 'OTP_INVALID',
                    'message' => 'Invalid OTP'
                ]
            ], 400);
        }

        // 2️⃣ ساخت JWT و ذخیره در Cache
        $token = JWTAuth::fromUser($user);
        Cache::put('jwt_token_' . $user->id, $token, now()->addHours(2));

        // حذف OTP بعد از استفاده
        Cache::forget('otp_login_' . $request->phone);

        // ✅ اینجا کاربر رو در سشن لاگین کن
        Auth::login($user);
        session()->regenerate(); // 🔒 برای امنیت
        session()->save();

        // 3️⃣ نقش‌ها و مسیر
        $roles = $user->roles->pluck('name');
        if ($roles->contains('ادمین')) {
            $redirect = 'admin.users';
        } elseif ($user->roles->pluck('id')->contains(3)) {
            $redirect = 'chat';
        } elseif ($user->roles()->where('allow_ticket', 1)->exists()) {
            $redirect = 'admin.tickets';
        } else {
            return response()->json([
                'error' => [
                    'code' => 'PERMISSION_DENIED',
                    'message' => 'User does not have permission to access any page'
                ]
            ], 403);
        }

        return response()->json([
            'access_token' => $token,
            'primary_role' => $user->roles->pluck('name'),
            'redirect_url' => route($redirect),
        ]);
    }


    public function twoFaVerify(Request $request)
    {
        // Verify 2FA code
    }

    public function activate(RegisterOtpRequest $request) // جدید: فعال‌سازی با OTP
    {
        if (Cache::get('otp_register_' . $request->phone) == $request->otp) {
            $user = User::where('phone', $request->phone)->first();
            $user->kyc_status = 'verified'; // یا active flag اضافه کن
            $user->save();
            $token = $user->createToken('api');
            return ['access_token' => $token->plainTextToken];
        }
        return response()->json(['error' => ['code' => 'OTP_INVALID', 'message' => 'Invalid OTP']], 400);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // خروج کاربر از سشن

        $request->session()->invalidate(); // حذف کامل داده‌های سشن
        $request->session()->regenerateToken(); // جلوگیری از CSRF بعد از logout

        return redirect('/login')->with('success', 'با موفقیت خارج شدید.');
    }
}
