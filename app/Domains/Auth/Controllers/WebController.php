<?php

namespace App\Domains\Auth\Controllers;

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
        return view('admin.tickets');
    }
    public function showUsers()
    {
        $users = User::with('roles')->get();

        return view('admin.users', compact('users'));
    }

    public function showRoles()
    {
        return view('admin.roles');
    }

    public function showChat()
    {
        return view('chat.index');
    }
}
