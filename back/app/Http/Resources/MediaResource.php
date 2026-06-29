<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'collection_name' => $this->collection_name,
            'url' => $this->getFullUrl(),
            'created_at' => $this->created_at,
            'type' => $this->getTypeFromMime()
        ];
    }
}
