<?php

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReferralCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public \App\Domains\Shared\Models\Referral $referral) {}

    public function broadcastOn(): array
    {
        return [ new PrivateChannel('role.' . $this->referral->assigned_role) ];
    }

    public function broadcastAs(): string
    {
        return 'referral.created';
    }

    public function broadcastWith(): array
    {
        return [
            'referral_id'       => $this->referral->id,
            'conversation_id'   => $this->referral->conversation_id,
            'trigger_message_id'=> $this->referral->trigger_message_id,
            'role'              => $this->referral->assigned_role,
            'status'            => $this->referral->status,
        ];
    }
}

