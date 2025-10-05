<?php

namespace App\Domains\Shared\Events;

use App\Domains\Shared\Models\Referral;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReferralCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Referral $referral) {}

    public function broadcastOn()
    {
        // همان الگوی شما برای MessageSent: حضور در کانال گفت‌وگو
        return new PresenceChannel('conversation.' . $this->referral->conversation_id);
    }

    public function broadcastAs()
    {
        return 'referral.created';
    }

    public function broadcastWith(): array
    {
        // دیتا مختصر و کافی برای UI
        return [
            'id'               => $this->referral->id,
            'conversation_id'  => $this->referral->conversation_id,
            'trigger_message_id'=> $this->referral->trigger_message_id,
            'user_id'          => $this->referral->user_id,
            'assigned_role'    => $this->referral->assigned_role,
            'assigned_agent_id'=> $this->referral->assigned_agent_id,
            'description'      => $this->referral->description,
            'status'           => $this->referral->status,
            'response_visibility' => $this->referral->response_visibility,
            'created_at'       => optional($this->referral->created_at)?->toISOString(),
        ];
    }
}
