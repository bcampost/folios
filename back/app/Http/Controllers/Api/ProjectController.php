<?php

namespace App\Http\Controllers\Api;

use App\Enums\BranchEnum;
use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\Project;
use App\Enums\FolioTypeEnum;
use App\Mail\PrevioAprobado;
use App\Mail\PrevioSolicitado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Enums\FolioClassificationEnum;
use App\Mail\FolioAprobadoFromProject;
use App\Http\Resources\ProjectResource;
use App\Mail\FolioSolicitadoFromProject;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\Http\Requests\Api\Project\StoreProjectRequest;

class ProjectController extends ApiController
{
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();

        $project = DB::transaction(function () use ($data, $request) {
            $project = Project::create([
                'value' => $data['value'] ?? 0,
                'deal_id' => $data['deal_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'channel' => $data['channel'],
                'discount' => $data['discount'] ?? null,
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'modality' => $data['modality'] ?? null,
                'negotiated_days' => $data['negotiated_days'] ?? null,
                'payment_by_customer_platform' => $data['payment_by_customer_platform'] ?? null,
                'owner_id' =>  auth()->user()->id
            ]);

            foreach($data['previos'] as $previo) {

                // $isApprovedState = (
                //     $request->user()->role !== RoleEnum::Advisor ||
                //     in_array(
                //         $previo['classification'], [
                //             FolioClassificationEnum::A->value
                //         ]
                //     )
                // ) ? true : false;

                $state = $this->getFolioState($previo);
                $priceData = $this->getProductPriceData($previo);

                $folio = $project->folios()->create([
                    'width' => $previo['width'] ?? null,
                    'height' => $previo['height'] ?? null,
                    'depth' => $previo['depth'] ?? null,
                    'quantity' => $previo['quantity'],
                    'melamina_color' => $previo['melamina_color'],
                    'melamina_density' => $previo['melamina_density'] ?? null,
                    'chapacinta_color' => $previo['chapacinta_color'],
                    'structure_color' => $previo['structure_color'],
                    'tela_color' => $previo['tela_color'],
                    'package_type' => $previo['package_type'],
                    'classification' => $previo['classification'],
                    'description' => $previo['description'],
                    'comments' => $previo['comments'] ?? null,
                    'acabados' => $previo['acabados'] ?? null,
                    'reference_product' => $previo['reference_product'] ?? null,
                    // 'state' => !$isApprovedState ? PrevioSolicitadoState::getStateId() : PrevioAprobadoState::getStateId(),
                    'state' => $state,
                    'assembly_number' => $previo['assembly_number'] ?? null,
                    'list_price' => $priceData['price'],
                    'cost' => $priceData['cost'],
                    'type' => $this->getType($previo),
                    'title' => $previo['title'] ?? null,
                    'category_id' => $previo['category_id'] ?? null,
                ]);

                if ($state !== PrevioSolicitadoState::getStateId()) {
                    $folio->assignCodeByType();
                }
                $folio->save();

                $this->syncFolioProductToCotizador($folio);

                if(isset($previo['images'])) {
                    foreach ($previo['images'] as $image) {
                        $folio->addMedia($image)->toMediaCollection('previo_image_reference');
                    }
                }

                if (isset($previo['audio'])) {
                    $folio->addMedia($previo['audio'])->toMediaCollection('previo_audio');
                }

                if ($state !== PrevioSolicitadoState::getStateId()) {
                    $folio->transitions()->create([
                        'prev_state_id' => PrevioSolicitadoState::getStateId(),
                        'next_state_id' => $state,
                        'user_id' => $request->user()->id
                    ]);
                }

            }

            return $project;
        });

        if($project->folios()->where('state', FolioSolicitadoState::getStateId())->count() > 0) {
            try {
                Mail::to([
                    ...$request->user()->managers()->get(),
                    ...$request->user()->leaders,
                ])->send(new FolioSolicitadoFromProject($project));
            } catch (\Exception $e) {

            }
        }

        if($project->folios()->where('state', FolioAprobadoState::getStateId())->count() > 0) {
            try {
                Mail::to([...$request->user()->engineering()->get()])->send(new FolioAprobadoFromProject($project));
            } catch (\Exception $e) {

            }
        }

        if($project->folios()->where('state', PrevioSolicitadoState::getStateId())->count() > 0) {
            if ($request->user()->role === RoleEnum::Advisor) {
                try {
                    Mail::to([
                        ...$request->user()->managers()->get(),
                        ...$request->user()->leaders,
                    ])->send(new PrevioSolicitado($project));
                } catch (\Exception $e) {

                }
            }
        }

        if ($project->folios()->where('state', PrevioAprobadoState::getStateId())->count() > 0) {
            try {
                Mail::to([...$request->user()->engineering()->get()])->send(new PrevioAprobado(
                    $project->folios()->where('state', PrevioAprobadoState::getStateId())->get()
                ));
            } catch (\Exception $e) {
                throw $e;
            }
        }

        // try {
        //     Mail::to([...$request->user()->engineering()->get()])->send(new PrevioAprobadoFromProject($project));
        // } catch (\Exception $e) {

        // }

        // if ($request->user()->role === RoleEnum::Advisor) {
        //     try {
        //         Mail::to($request->user()->managers()->get())->send(new PrevioSolicitado($project));
        //     } catch (\Exception $e) {

        //     }
        // } else {
        //     try {
        //         Mail::to([...$request->user()->engineering()->get()])->send(new PrevioAprobadoFromProject($project));
        //     } catch (\Exception $e) {

        //     }
        // }

        return new ProjectResource($project);
    }

    private function getFolioState(array $previo)
    {
        $state = PrevioSolicitadoState::getStateId();

        if(
            auth()->user()->role !== RoleEnum::Advisor
        ) {
            if(
                auth()->user()->role === RoleEnum::TeamLeader &&
                auth()->user()->branch == BranchEnum::CDMX->value
            ) {
               $state = $previo['classification'] === FolioClassificationEnum::A->value ? FolioSolicitadoState::getStateId() : PrevioSolicitadoState::getStateId();
            } else {

                $state = $previo['classification'] === FolioClassificationEnum::A->value ? FolioAprobadoState::getStateId() : PrevioAprobadoState::getStateId();
            }

        } else {
            $state = $previo['classification'] === FolioClassificationEnum::A->value ? FolioSolicitadoState::getStateId() : PrevioSolicitadoState::getStateId();
        }

        return $state;
    }

    private function getProductPriceData(array $previo): array
    {
        $result = ['price' => null, 'cost' => null];

        // Solo asignar precio y costo si la clasificación es A y hay producto de referencia
        if (!isset($previo['reference_product']) || $previo['classification'] !== FolioClassificationEnum::A->value) {
            return $result;
        }

        $product = Product::find($previo['reference_product']);

        if ($product) {
            $result['price'] = $product->price;
            $result['cost'] = $product->cost;
        }

        return $result;
    }

    private function getType(array $previo)
    {
        return $previo['classification'] === FolioClassificationEnum::A->value ? FolioTypeEnum::Folio : FolioTypeEnum::Previo;
    }

    private function syncFolioProductToCotizador($folio): void
    {
        if ($folio->type !== FolioTypeEnum::Folio || !$folio->folio_code) {
            return;
        }

        try {
            $owner = $folio->project->owner;

            if (!$owner) {
                return;
            }

            $cotizadorUser = DB::connection('cotizador')
                ->table('users')
                ->where('main_user_id', $owner->main_user_id)
                ->first();

            if (!$cotizadorUser) {
                return;
            }

            $title = $folio->title
                ?? $folio->product?->title
                ?? 'Producto sin título';

            $categoryId = $folio->category_id
                ?? $folio->product?->category_id;

            Product::updateOrCreate(
                [
                    'sku'      => $folio->folio_code,
                    'owner_id' => $cotizadorUser->id,
                ],
                [
                    'title'       => $title,
                    'description' => $folio->description,
                    'cost'        => $folio->cost,
                    'price'       => $folio->list_price,
                    'category_id' => $categoryId,
                    'image'       => $folio->product?->image,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error al crear producto folio en cotizador: ' . $e->getMessage());
        }
    }
}
