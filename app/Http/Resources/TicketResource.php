<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'sender_type' => $this->sender_type,
            'department' => $this->department,
            'status' => $this->status,
            'priority' => $this->priority,
            'created_at' => $this->created_at->toISOString(),
            'attachments_count' => (int) ($this->attachments_count ?? 0),
            'effective_status' => $this->effective_status,   // ⬅️ مهم
            'last_sender_type' => $this->last_sender_type,   // اختیاری برای UI
            'attachments' => FileResource::collection($this->whenLoaded('media')),
            'replies' => TicketMessageResource::collection($this->whenLoaded('replies'))
        ];
    }
}
