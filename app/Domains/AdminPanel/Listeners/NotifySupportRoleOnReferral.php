<?php namespace App\Domains\AdminPanel\Listeners;

use App\Domains\Shared\Events\ReferralCreated;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReferralAssigned;

class NotifySupportRoleOnReferral
{
    public function handle(ReferralCreated $event): void
    {
        $role = $event->referral->assigned_role;
        $users = \App\Domains\Shared\Models\User::role($role)->get();
        if ($users->isEmpty()) return;

        Notification::send($users, new ReferralAssigned($event->referral));
    }
}
