<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Models\Product;
use App\Mail\LiberadoParaFacturar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\LiberadoParaFacturarState;

class FolioAprobadoToLiberadoParaFacturar extends Transition
{
    protected Folio $folio;

    protected User $user;

    public function __construct(Folio $folio, User $user)
    {
        $this->folio = $folio;
        $this->user = $user;
    }

    public function handle() : Folio
    {
        $this->createTransition(
            FolioAprobadoState::class,
            LiberadoParaFacturarState::class,
            $this->user
        );

        $this->folio->state = LiberadoParaFacturarState::class;
        $this->folio->save();

        try {
            $owner = $this->folio->project->owner;

            if ($owner) {
                $cotizadorUser = DB::connection('cotizador')
                    ->table('users')
                    ->where('main_user_id', $owner->main_user_id)
                    ->first();

                if ($cotizadorUser) {
                    $existing = Product::where('sku', $this->folio->previo_code)
                        ->where('owner_id', $cotizadorUser->id)
                        ->first();

                    if ($existing) {
                        $existing->sku = $this->folio->folio_code;
                        $existing->save();
                    } else {
                        $title = $this->folio->title
                            ?? $this->folio->product?->title
                            ?? 'Producto sin título';

                        $categoryId = $this->folio->category_id
                            ?? $this->folio->product?->category_id;

                        Product::updateOrCreate(
                            [
                                'sku'      => $this->folio->folio_code,
                                'owner_id' => $cotizadorUser->id,
                            ],
                            [
                                'title'       => $title,
                                'description' => $this->folio->description,
                                'cost'        => $this->folio->cost,
                                'price'       => $this->folio->list_price,
                                'category_id' => $categoryId,
                                'image'       => $this->folio->product?->image,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            //
        }

        try {
            Mail::to($this->folio->owner)->send(new LiberadoParaFacturar($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
