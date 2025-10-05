<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->file_name,
            'mime' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getUrl()
        ];
    }
}
