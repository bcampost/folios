<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dealableType = $this->getDealableType();

        $dealable = match ($dealableType) {
                'customer' => new CustomerResource($this->whenLoaded('customer')),
                'lead' => new LeadResource($this->whenLoaded('lead')),
                default => null,
            };

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'lead_id' => $this->lead_id,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'project_value' => $this->project_value,
            'dealable_type' => $dealableType,
            'dealable' => $dealable,
            'quote_authorized_at' => $this->quote_authorized_at,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
        ];
    }
}
