<?php

namespace App\Http\Resources;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatar_url' => $this->avatar_url,
            'is_distributor' => $this->is_distributor,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'managers' => $this->managers()->get(),
            'assigned_users' => $this->assignedUsers()->get()
        ];
    }
}
