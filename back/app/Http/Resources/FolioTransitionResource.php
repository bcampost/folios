<?php

namespace App\Http\Resources;

use App\States\Folio\FolioState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolioTransitionResource extends JsonResource
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
            'prev_state' => new FolioStateResource(FolioState::resolve($this->prev_state_id)),
            'next_state' => new FolioStateResource(FolioState::resolve($this->next_state_id)),
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user'))
        ];
    }
}
