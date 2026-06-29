<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;

class ProductResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'sku' => $this->sku,
            'type' => $this->type,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'width' => $this->width,
            'height' => $this->height,
            'depth' => $this->depth,
            'weight' => $this->weight,
            'melamina_density' => $this->melamina_density,
            'category_id'      => $this->category_id,
            'category'         => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
