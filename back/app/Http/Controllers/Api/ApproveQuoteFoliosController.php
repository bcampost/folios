<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\Folio;
use App\Models\Product;
use App\Models\User;
use App\Enums\FolioTypeEnum;
use App\Mail\FolioAprobado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\FolioResource;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\PrecioDeListaAsignadoState;
use App\Http\Requests\Api\ApproveQuoteFoliosRequest;

class ApproveQuoteFoliosController extends ApiController
{
    public function __invoke(ApproveQuoteFoliosRequest $request)
    {
        $deal = Deal::findOrFail($request->deal_id);

        if (!$deal->quote_id) {
            return response()->json([
                'message' => 'Deal does not have an authorized quote.',
                'folios' => [],
                'count' => 0,
            ]);
        }

        $crmUser = DB::connection('crm')
            ->table('users')
            ->where('id', $deal->owner_id)
            ->first();

        $cotizadorUser = $crmUser ? DB::connection('cotizador')
            ->table('users')
            ->where('main_user_id', $crmUser->main_user_id)
            ->first() : null;

        // if (!$cotizadorUser) {
        //     return response()->json([
        //         'message' => 'Cotizador user mapping not found for deal owner.',
        //     ], 400);
        // }

        $query = DB::connection('cotizador')
            ->table('quote_items')
            ->where('quote_items.quote_id', $deal->quote_id)
            ->join('products', 'products.id', '=', 'quote_items.product_id');

        // if ($cotizadorUser) {
        //     $query->where('products.owner_id', $cotizadorUser->id);
        // }

        $skus = $query->pluck('products.sku')
            ->filter()
            ->unique()
            ->values();

        if ($skus->isEmpty()) {
            return response()->json([
                'message' => 'No product SKUs found for this quote.',
                'folios' => [],
                'count' => 0,
            ]);
        }

        $allowedStates = [
            PrevioSolicitadoState::getStateId(),
            PrevioAprobadoState::getStateId(),
            CostoAsignadoState::getStateId(),
            PrecioDeListaAsignadoState::getStateId(),
        ];

        $folios = Folio::query()
            ->whereIn('previo_code', $skus)
            ->where('type', FolioTypeEnum::Previo)
            ->whereIn('state', $allowedStates)
            ->whereHas('project', fn ($q) => $q->where('deal_id', $deal->id))
            ->get();

        if ($folios->isEmpty()) {
            return response()->json([
                'message' => 'No matching previo folios found.',
                'folios' => [],
                'count' => 0,
            ]);
        }

        $user = auth()->user();
        $engineeringUsers = User::engineering()->get();

        foreach ($folios as $folio) {
            $folio->transitions()->create([
                'prev_state_id' => $folio->state->getId(),
                'next_state_id' => FolioAprobadoState::getStateId(),
                'user_id' => $user?->id,
            ]);

            $folio->type = FolioTypeEnum::Folio;
            $folio->state = FolioAprobadoState::class;
            $folio->assignCodeByType();
            $folio->save();

            $this->updateProductForFolio($folio);

            try {
                $recipients = collect($engineeringUsers);

                if ($folio->owner) {
                    $recipients->prepend($folio->owner);
                }

                Mail::to($recipients->all())->send(new FolioAprobado($folio));
            } catch (\Exception $e) {
                //
            }
        }

        return response()->json([
            'message' => 'Folios transitioned to approved.',
            'folios' => FolioResource::collection($folios),
            'count' => $folios->count(),
        ]);
    }

    private function updateProductForFolio(Folio $folio): void
    {
        try {
            $owner = $folio->project->owner;

            if ($owner) {
                $cotizadorUser = DB::connection('cotizador')
                    ->table('users')
                    ->where('main_user_id', $owner->main_user_id)
                    ->first();

                if ($cotizadorUser) {
                    $existing = Product::where('sku', $folio->previo_code)
                        ->where('owner_id', $cotizadorUser->id)
                        ->first();

                    if ($existing) {
                        $existing->sku = $folio->folio_code;
                        $existing->save();
                    } else {
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
                    }
                }
            }
        } catch (\Exception $e) {
            //
        }
    }
}
