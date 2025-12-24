<?php

namespace App\Notifications;

use App\Domains\Shared\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketRespondedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket, public Ticket $reply)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $excerpt = Str::limit($this->reply->message ?? '', 90);
        return [
            'title' => 'پاسخ پشتیبان',
            'body' => "پشتیبان به تیکت «{$this->ticket->title}» پاسخ داد: «{$excerpt}»",
            'title_key' => 'notifications.ticketRespondedTitle',
            'body_key' => 'notifications.ticketRespondedBody',
            'params' => [
                'ticketTitle' => $this->ticket->title,
                'excerpt' => $excerpt,
            ],
            'category' => 'ticket',
            'ticket_id' => $this->ticket->id,
            'related_id' => $this->ticket->id,
            'meta' => [
                'reply_id' => $this->reply->id,
            ],
        ];
    }
}
