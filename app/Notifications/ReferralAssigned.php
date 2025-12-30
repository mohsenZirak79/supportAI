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
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $conversationTitle = optional($this->referral->conversation)->title ?: 'گفت‌وگو';
        return [
            'title' => 'ارجاع جدید',
            'body' => "ارجاع جدیدی برای «{$conversationTitle}» به شما رسید.",
            'title_key' => 'notifications.referralAssignedTitle',
            'body_key' => 'notifications.referralAssignedBody',
            'params' => [
                'conversationTitle' => $conversationTitle,
            ],
            'category' => 'referral',
            'related_id' => $this->referral->conversation_id,
            'conversation_id' => $this->referral->conversation_id,
            'role' => $this->referral->assigned_role,
            'status' => $this->referral->status,
            'referral_id' => $this->referral->id,
            'meta' => [
                'trigger_message_id' => $this->referral->trigger_message_id,
            ]
        ];
    }
}
