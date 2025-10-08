<?php

namespace App\Domains\AdminPanel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // نمایش فرم ویرایش پروفایل
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    // بروزرسانی اطلاعات کاربر
    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'national_id' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'role' => 'in:user,admin',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // اگر پسورد پر شده بود، رمز جدید اعمال می‌شود
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('profile')->with('success', 'اطلاعات شما با موفقیت بروزرسانی شد.');
    }
}
