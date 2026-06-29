<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;

class FolioResource extends JsonResource
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
            'state' => new FolioStateResource($this->state),
            'type' => $this->type,
            'classification' => $this->classification,
            'previo_code' => $this->previo_code,
            'folio_code' => $this->folio_code,
            'is_existing_folio' => $this->is_existing_folio,
            'suggested_folio_code' => $this->when(
                !$this->folio_code,
                fn () => $this->getSuggestedFolioCode()
            ),
            'list_price' => $this->list_price,
            'cost' => $this->cost,
            'cost_details' => $this->cost_details,
            'list_price_details' => $this->list_price_details,
            'reason_for_rejection' => $this->reason_for_rejection,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'description' => $this->description,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'quantity' => $this->quantity,
            'height' => $this->height,
            'width' => $this->width,
            'depth' => $this->depth,
            'melamina_color' => $this->melamina_color,
            'melamina_density' => $this->melamina_density,
            'chapacinta_color' => $this->chapacinta_color,
            'structure_color' => $this->structure_color,
            'tela_color' => $this->tela_color,
            'package_type' => $this->package_type,
            'assembly_number' => $this->assembly_number,
            'acabados' => $this->acabados,
            'customer_name' => $this->when($this->customer_name, $this->customer_name),
            'deal_name' => $this->when($this->deal_name, $this->deal_name),
            'product_sku' => $this->when($this->product_sku, $this->product_sku),
            'screw_kits' => $this->screw_kits ? $this->screw_kits : [],
            'delivery_timing' => $this->getDeliveryTiming(),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'transitions' => FolioTransitionResource::collection($this->whenLoaded('transitions')),
            'last_transition' => new FolioTransitionResource($this->whenLoaded('lastTransition')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assembly_siblings' => FolioResource::collection($this->whenLoaded('assemblySiblings')),
            // 'images' => $this->when($this->relationLoaded('media') && $this->getFirstMedia('previo_image_reference'), function () {
            //     return $this->getFirstMediaUrl('previo_image_reference');
            // }),
            'images' => MediaResource::collection($this->whenLoaded('mediaReferenceFiles')),
            'audio' => $this->when(
                $this->relationLoaded('media') && $this->getFirstMedia('previo_audio'),
                fn () => [
                    'url'      => $this->getFirstMediaUrl('previo_audio'),
                    'filename' => $this->getFirstMedia('previo_audio')?->file_name,
                ]
            ),
            'caratula_url' => $this->when($this->relationLoaded('media') && $this->getFirstMedia('caratula'), function () {
                return $this->getFirstMediaUrl('caratula');
            }),
            'reference_image_url' => $this->when($this->relationLoaded('media') && $this->getFirstMedia('main_reference_image'), function () {
                return $this->getFirstMediaUrl('main_reference_image');
            }),

        ];
    }
}
