<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'old' => count($this->changes()['old']) > 0 ? $this->changes()['old'] : null,
            'new' => count($this->changes()['attributes']) > 0 ? $this->changes()['attributes'] : null,
            'causer' => new UserResource($this->causer),
            'created_at' => $this->created_at
        ];
    }
}
