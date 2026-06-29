<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Mail\LIberadoParaProduccion;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\LiberadoParaFacturarState;
use App\States\Folio\LiberadoParaProduccionState;

class LiberadoParaFacturarToProduccion extends Transition
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
            LiberadoParaFacturarState::class,
            LiberadoParaProduccionState::class,
            $this->user
        );

        $this->folio->state = LiberadoParaProduccionState::class;
        $this->folio->save();

        try {
            Mail::to([
                'dreyes@crisa.com.mx',
                $this->folio->owner
            ])->send(new LIberadoParaProduccion($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
