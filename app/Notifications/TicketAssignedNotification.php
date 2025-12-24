<?php

namespace App\Notifications;

use App\Domains\Shared\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'تیکت جدید',
            'body' => "تیکت «{$this->ticket->title}» برای بخش شما ثبت شد.",
            'title_key' => 'notifications.ticketAssignedTitle',
            'body_key' => 'notifications.ticketAssignedBody',
            'params' => [
                'ticketTitle' => $this->ticket->title,
            ],
            'category' => 'ticket',
            'ticket_id' => $this->ticket->id,
            'related_id' => $this->ticket->id,
            'meta' => [
                'priority' => $this->ticket->priority,
                'department' => $this->ticket->department,
            ],
        ];
    }
}
