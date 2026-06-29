<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'value' => $this->value,
            'channel' => $this->channel,
            'discount' => $this->discount,
            'modality' => $this->modality,
            'negotiated_days' => $this->negotiated_days,
            'payment_by_customer_platform' => $this->payment_by_customer_platform,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'deal' => new DealResource($this->whenLoaded('deal')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'payment_term' => new PaymentTermResource($this->whenLoaded('paymentTerm'))
        ];
    }
}
