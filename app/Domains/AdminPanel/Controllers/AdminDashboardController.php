<?php

namespace App\Domains\AdminPanel\Controllers;

use App\Domains\Shared\Models\Conversation;
use App\Domains\Shared\Models\Referral;
use App\Domains\Shared\Models\Ticket;
use App\Domains\Shared\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        // ادمین سامانه (ادمین، برنامه‌نویس، یا internal) به تمام تیکت‌ها دسترسی دارد
        $hasInternal = $user->roles()->where('is_internal', 1)->exists();
        $isAdmin = $user->hasRole('ادمین') || $user->hasRole('برنامه نویس') || $hasInternal;

        $ticketsQuery = Ticket::query()->whereNull('parent_id');
        if (!$isAdmin) {
            $roleIds = $user->roles()->pluck('id')->all();
            $ticketsQuery->whereIn('department_role_id', $roleIds);
        }

        $stats = [
            'tickets_total'   => (clone $ticketsQuery)->count(),
            'tickets_pending' => (clone $ticketsQuery)->where('status', 'pending')->count(),
            'conversations_total' => Conversation::query()->count(),
            'referrals_open'  => Referral::query()->whereIn('status', ['pending', 'assigned'])->count(),
        ];

        if ($isAdmin) {
            $stats['users_total'] = User::query()->count();
        }

        $recentTickets = (clone $ticketsQuery)
            ->with('sender:id,name')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'isAdmin' => $isAdmin,
        ]);
    }
}
