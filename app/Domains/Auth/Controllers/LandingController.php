<?php

namespace App\Domains\Auth\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Domains\Shared\Models\User;
use App\Http\Controllers\Controller;

class LandingController extends Controller
{
    /**
     * Show the landing page
     * If user is authenticated, redirect to appropriate dashboard
     */
    public function index()
    {
        // If user is already authenticated, redirect to appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user has admin or programmer role
            if ($user->hasRole('ادمین') || $user->hasRole('برنامه نویس')) {
                return redirect()->route('admin.dashboard');
            }
            
            // Regular users go to chat page
            return redirect()->route('chat');
        }

        return view('landing');
    }

    /**
     * Handle login with email/password
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'remember' => 'nullable|boolean',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember', false);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            if ($user->hasRole('ادمین') || $user->hasRole('برنامه نویس')) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Regular users go to chat page
            return redirect()->intended(route('chat'));
        }

        return redirect()->route('landing')
            ->withErrors(['email' => 'اطلاعات ورود صحیح نیست.'])
            ->withInput($request->only('email'))
            ->with('login_errors', true);
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign default role (user role with id 3)
        $role = \Spatie\Permission\Models\Role::find(3);
        if ($role) {
            $user->assignRole($role);
        }

        // Auto-login after registration
        Auth::login($user);

        $request->session()->regenerate();

        // Redirect based on user role
        if ($user->hasRole('ادمین') || $user->hasRole('برنامه نویس')) {
            return redirect()->route('admin.dashboard')->with('success', 'ثبت‌نام با موفقیت انجام شد.');
        }

        return redirect()->route('chat')->with('success', 'ثبت‌نام با موفقیت انجام شد.');
    }
}

