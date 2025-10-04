<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Role\Models\Role;
use App\Domains\Shared\Models\Ticket;
use App\Domains\Shared\Models\User;
use Illuminate\Http\Request;

class WebController
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showAdmin()
    {
        return view('admin.dashboard');
    }
    public function showTickets()
    {
        $tickets = Ticket::with('sender')->latest()->get();

        // همه کاربران با نقش‌ها
        $users = User::with('roles')->get();

        // همه نقش‌ها با تعداد کاربران
        $roles = Role::withCount('users')->get();

        return view('admin.tickets', compact('tickets', 'users', 'roles'));
    }
    public function showUsers()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        return view('admin.users', compact('users' , 'roles'));
    }

    public function showRoles()
    {
        // نقش‌ها همراه با تعداد کاربران
        $roles = Role::withCount('users')
            ->where('id', '!=', 1)
            ->get();
        return view('admin.roles', compact('roles'));
    }

    public function showChat()
    {
        return view('chat.index');
    }
}
