<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use App\Models\Product;
use App\Mail\FolioExistenteLiberadoParaFacturar;
use App\Services\BusinessCentralProductMeasuresService;
use App\Http\Resources\FolioResource;
use App\Http\Requests\Api\Folio\AssignExistingFolioRequest;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\LiberadoParaFacturarState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AssignExistingFolioController extends ApiController
{
    public function __invoke(AssignExistingFolioRequest $request, Folio $folio, BusinessCentralProductMeasuresService $bcService)
    {
        $sku = $request->input('sku');

        $folio->folio_code = $sku;
        $folio->is_existing_folio = true;
        $folio->state = LiberadoParaFacturarState::class;

        $folio->transitions()->create([
            'prev_state_id' => FolioAprobadoState::getStateId(),
            'next_state_id' => LiberadoParaFacturarState::getStateId(),
            'user_id' => $request->user()->id,
        ]);

        $folio->save();

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

        try {
            Mail::to($folio->owner)->send(new FolioExistenteLiberadoParaFacturar($folio));
        } catch (\Exception $e) {
            //
        }

        return new FolioResource($folio);
    }
}
