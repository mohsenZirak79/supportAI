<?php

namespace App\Domains\Shared\Controllers;

use App\Domains\Auth\Requests\RegisterRequest;
use App\Domains\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController
{
    /**
     * لیست کاربران
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * فرم ایجاد کاربر
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * ذخیره کاربر جدید
     */
    public function store(RegisterRequest $request)
    {
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

        $role = \Spatie\Permission\Models\Role::find($request->role);
        if ($role) {
            $user->assignRole($role);
        }

        return redirect()->route('admin.users')->with('success', 'کاربر با موفقیت ایجاد شد.');
    }

    /**
     * فرم ویرایش کاربر
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * آپدیت اطلاعات کاربر
     */
    public function update(Request $request, User $user)
    {
        $blockedUuids = [
            '96998813-6a6d-4b36-b3dc-662ec3e68dfd',
            '7f25cceb-3b3d-4ee7-894b-162218c7860f',
        ];

        if (in_array($user->id, $blockedUuids, true)) {
            return back()->withErrors([
                'error' => 'امکان حذف این کاربر وجود ندارد.',
            ]);
        }

        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $user->id,
            'phone'  => 'required|unique:users,phone,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return redirect()->route('users.index')->with('success', 'کاربر با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف کاربر
     */
    public function destroy(User $user)
    {
        $blockedUuids = [
            '96998813-6a6d-4b36-b3dc-662ec3e68dfd',
            '7f25cceb-3b3d-4ee7-894b-162218c7860f',
        ];

        if (in_array($user->id, $blockedUuids, true)) {
            return back()->withErrors([
                'error' => 'امکان حذف این کاربر وجود ندارد.',
            ]);
        }
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'کاربر حذف شد.');
    }
}
