<?php

namespace App\Domains\Shared\Events;

use App\Domains\Shared\Models\Message;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public string $id;
    public string $conversationId;
    public ?string $senderId;
    public string $type;
    public string $createdAt;

    public function __construct(Message $message)
    {
        $this->id            = (string) $message->id;
        $this->conversationId= (string) $message->conversation_id;
        $this->senderId      = $message->sender_id ? (string) $message->sender_id : null;
        $this->type          = (string) $message->type;
        $this->createdAt     = optional($message->created_at)->toISOString() ?? now()->toISOString();
    }

    public function broadcastOn()
    {
        // اگر حضور (presence) لازم داری همان بماند
        return new PresenceChannel('conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        // بدون content، بدون media؛ فقط کمینه‌ی لازم
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversationId,
            'sender_id'       => $this->senderId,
            'type'            => $this->type,
            'created_at'      => $this->createdAt,
        ];
    }
}
