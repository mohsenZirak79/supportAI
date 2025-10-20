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
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Cookie;
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
        $jalali = $request->input('birth_date');
        $carbon = Jalalian::fromFormat('Y/m/d', $jalali)->toCarbon();

        $user = User::create([
            'name' => $request->name,
            'family' => $request->family,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'national_id' => $request->national_id,
            'postal_code' => $request->postal_code,
            'birth_date' => $carbon,
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
            'phone' => ['required', 'regex:/^(\+98|0)?9\d{9}$/'],
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
        if (!$user) {
            return response()->json(['message' => 'شماره تلفن یافت نشد'], 404);
        }

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

        if (!$user || now()->diffInSeconds($user->otp_sent_at) > 120) {
            return response()->json(['error' => ['code' => 'OTP_EXPIRED', 'message' => 'OTP expired']], 400);
        }

        if (Cache::get('otp_login_' . $request->phone) != $request->otp) {
            return response()->json(['error' => ['code' => 'OTP_INVALID', 'message' => 'Invalid OTP']], 400);
        }

        // 1) صدور JWT برای استفاده در API
        $jwt = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        // 2) ساخت Session برای وب (ادمین/ریورب/چیزهایی که به سشن تکیه دارند)
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        Cache::forget('otp_login_' . $request->phone);

        // 3) تصمیم مسیر
        $isAdmin = $user->hasRole('کاربر عادی'); // یا هر رول/پرمیژنِ دلخواه
        $redirect = $isAdmin ? route('chat') : route('profile');
//        $jwt = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
//        \Log::debug('JWT issued', ['len' => strlen($jwt), 'head' => substr($jwt,0,20)]);
//        $redirect = route('_debug-jwt');

        $response = response()->json([
            'access_token' => $jwt,
            'role_names' => $user->roles->pluck('name'),
            'redirect_url' => $redirect,
        ]);

        // 5) ست کردن کوکی JWT به صورت *خام (Raw)*
        $cookie = Cookie::make(
            'jwt',
            $jwt,
            120,                       // دقیقه
            '/',                       // path
            null,                      // domain
            app()->isProduction(),     // Secure فقط روی HTTPS
            true,                      // HttpOnly
            true,                      // ⬅️ **RAW/UNENCRYPTED** (بسیار مهم)
            'Lax'                      // SameSite
        );

        // 6) افزودن کوکی خام به پاسخ
        return $response->withCookie($cookie);




        // 4) پاسخ + ست کردن کوکی JWT (برای API)
//        return response()->json([
//            'access_token' => $jwt,
//            'role_names' => $user->roles->pluck('name'),
//            'redirect_url' => $redirect,
//        ])->cookie('jwt', $jwt, 120, '/', null, app()->isProduction(), true, true, 'Lax');
//
//        $redirect = route('chat');
//
//        return response()->json([
//            'access_token' => $jwt,
//            'role_names' => $user->roles->pluck('name'),
//            'redirect_url' => $redirect,
//        ])->cookie(
//            'jwt',
//            $jwt,
//            120,                       // دقیقه
//            '/',                   // path
//            null,                  // domain
//            app()->isProduction(), // Secure فقط روی HTTPS
//            true,                      // HttpOnly
//            true,                 // Raw
//            'Lax'                      // SameSite
//        );
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
        // خروج سشن (برای ادمین)
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // پاک‌کردن JWT کوکی
        return redirect('/login')
            ->with('success', 'با موفقیت خارج شدید.')
            ->withoutCookie('jwt');
    }

    public function refresh(Request $request)
    {
        try {
            // اگر توکن منقضی شده هم باشد، parseToken()->refresh() کار می‌کند (اگر blacklist فعال است مطابق تنظیمات).
            $newToken = JWTAuth::parseToken()->refresh(); // می‌تواند exception بدهد اگر توکن totally invalid باشد
        } catch (JWTException $e) {
            return response()->json(['error' => ['code' => 'TOKEN_INVALID', 'message' => 'Cannot refresh token']], 401);
        }

        return response()->json(['ok' => true])->cookie(
            'jwt',
            $newToken,
            120,                           // عمر جدید (دقیقه)
            '/',
            null,
            app()->isProduction(),         // Secure در پرود
            true,                          // HttpOnly
            true,
            'Lax'
        );
    }
}
