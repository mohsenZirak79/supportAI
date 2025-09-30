<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'sender_type' => $this->sender_type,
            'created_at' => $this->created_at->toISOString(),
            'attachments' => FileResource::collection($this->whenLoaded('media'))
        ];
    }
}
