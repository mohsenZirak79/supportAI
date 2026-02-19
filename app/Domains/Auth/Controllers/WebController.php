<?php

namespace App\Domains\Auth\Controllers;

use App\Domains\Role\Models\Role;
use App\Domains\Shared\Models\Conversation;
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

    public function showTickets()
    {
        $tickets = Ticket::with('sender')->latest()->get();

        // همه کاربران با نقش‌ها
        $users = User::with('roles')->get();

        // همه نقش‌ها با تعداد کاربران
        $roles = Role::withCount('users')->get();

        return view('admin.tickets', compact('tickets', 'users', 'roles'));
    }
    public function showChats()
    {
        // همه چت‌ها با یوزر و پیام‌ها
        $conversation = Conversation::with(['user', 'messages'])
            ->latest()
            ->get();

        return view('admin.chats', compact('conversation'));
    }

    public function showUsers(Request $request)
    {
        $query = User::query()->with('roles');

        if (!auth()->user()->hasRole('برنامه نویس')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'برنامه نویس');
            });
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('family', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('national_id', 'like', $term);
            });
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::where('id', '<>', 1)->orderBy('name')->get();

        return view('admin.users', compact('users', 'roles'));
    }

    public function showRoles(Request $request)
    {
        $query = Role::withCount('users')->where('id', '!=', 1);

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where('name', 'like', $term);
        }

        $roles = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.roles', compact('roles'));
    }

    public function showChat()
    {
        return view('chat.index');
    }
}
