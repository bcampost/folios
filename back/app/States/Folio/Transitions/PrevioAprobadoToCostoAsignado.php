<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Mail\CostoAsignado;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\PrevioAprobadoState;

class PrevioAprobadoToCostoAsignado extends Transition
{
    protected Folio $folio;

    protected User $user;

    protected float|int $cost;

    protected ?string $cost_details;

    protected ?string $previo_code;

    protected float|int $list_price;

    public function __construct(
        Folio $folio,
        User $user,
        float|int $cost,
        ?string $cost_details
    )
    {
        $this->folio = $folio;
        $this->user = $user;
        $this->cost = $cost;
        $this->cost_details = $cost_details;
    }

    public function handle() : Folio
    {
        $this->createTransition(
            PrevioAprobadoState::class,
            CostoAsignadoState::class,
            $this->user
        );

        $this->folio->state = CostoAsignadoState::class;
        $this->folio->cost = $this->cost;
        $this->folio->cost_details = $this->cost_details;
        $this->folio->save();

        try {
            Mail::to([
                $this->folio->owner,
                ...$this->user->finance()->get()
            ])->send(new CostoAsignado($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
