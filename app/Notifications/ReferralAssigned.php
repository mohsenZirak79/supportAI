<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Domains\Shared\Models\Referral;

class ReferralAssigned extends Notification
{
    use Queueable;

    public function __construct(public Referral $referral)
    {
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // Reverb سازگار
    }

    public function toArray($notifiable)
    {
        return [
            'referral_id' => $this->referral->id,
            'conversation_id' => $this->referral->conversation_id,
            'trigger_message_id' => $this->referral->trigger_message_id,
            'role' => $this->referral->assigned_role,
            'status' => $this->referral->status,
        ];
    }
}
