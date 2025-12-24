<?php

namespace App\Notifications;

use App\Domains\Shared\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReferralRespondedNotification extends Notification
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
            'title' => 'پاسخ ارجاع',
            'body' => "پاسخ مربوط به ارجاع شما در «{$conversationTitle}» ثبت شد.",
            'title_key' => 'notifications.referralRespondedTitle',
            'body_key' => 'notifications.referralRespondedBody',
            'params' => [
                'conversationTitle' => $conversationTitle,
            ],
            'category' => 'referral',
            'conversation_id' => $this->referral->conversation_id,
            'referral_id' => $this->referral->id,
            'related_id' => $this->referral->conversation_id,
            'status' => $this->referral->status,
            'meta' => [
                'response_visibility' => $this->referral->response_visibility,
            ],
        ];
    }
}
