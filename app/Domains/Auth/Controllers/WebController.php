<?php

namespace App\Domains\Auth\Controllers;

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

    public function showChat()
    {
        return view('chat.index');
    }
}
