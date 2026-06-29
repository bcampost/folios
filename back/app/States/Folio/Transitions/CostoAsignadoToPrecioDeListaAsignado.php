<?php

namespace App\States\Folio\Transitions;

use App\Mail\PrecioListaAsignado;
use App\Models\Folio;
use App\Models\Product;
use App\Models\User;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\PrecioDeListaAsignadoState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CostoAsignadoToPrecioDeListaAsignado extends Transition
{
    protected Folio $folio;

    protected User $user;

    protected float|int $list_price;

    protected ?string $list_price_details;

    public function __construct(Folio $folio, User $user, float|int $list_price, ?string $list_price_details)
    {
        $this->folio = $folio;
        $this->user = $user;
        $this->list_price = $list_price;
        $this->list_price_details = $list_price_details;
    }

    public function handle() : Folio
    {
        $this->createTransition(
            CostoAsignadoState::class,
            PrecioDeListaAsignadoState::class,
            $this->user
        );

        $this->folio->state = PrecioDeListaAsignadoState::class;
        $this->folio->list_price = $this->list_price;
        $this->folio->list_price_details = $this->list_price_details;
        $this->folio->save();

        try {
            $owner = $this->folio->project->owner;

            if ($owner) {
                $cotizadorUser = DB::connection('cotizador')
                    ->table('users')
                    ->where('main_user_id', $owner->main_user_id)
                    ->first();

                if ($cotizadorUser) {
                    $title = $this->folio->title
                        ?? $this->folio->product?->title
                        ?? 'Producto sin título';

                    $categoryId = $this->folio->category_id
                        ?? $this->folio->product?->category_id;

                    Product::updateOrCreate(
                        [
                            'sku'      => $this->folio->previo_code,
                            'owner_id' => $cotizadorUser->id,
                        ],
                        [
                            'title'       => $title,
                            'description' => $this->folio->description,
                            'cost'        => $this->folio->cost,
                            'price'       => $this->folio->list_price,
                            'category_id' => $categoryId,
                            'image'       => $this->folio->product?->image
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al crear producto en cotizador: ' . $e->getMessage());
        }

        try {
            Mail::to($this->folio->owner)->send(new PrecioListaAsignado($this->folio));
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de precio de lista asignado: ' . $e->getMessage());
        }

        return $this->folio;
    }
}
