<?php namespace App\Domains\AdminPanel\Listeners;

use App\Domains\Shared\Events\ReferralCreated;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReferralAssigned;
use App\Domains\Shared\Models\User;

class NotifySupportRoleOnReferral
{
    public function handle(ReferralCreated $event): void
    {
        $assignedAgentId = $event->referral->assigned_agent_id;
        if (!$assignedAgentId) {
            return;
        }

        $user = User::find($assignedAgentId);
        if (!$user) {
            return;
        }

        Notification::send($user, new ReferralAssigned($event->referral));
    }
}
