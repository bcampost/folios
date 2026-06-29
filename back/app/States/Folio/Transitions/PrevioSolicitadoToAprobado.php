<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Mail\PrevioAprobado;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\PrevioSolicitadoState;

class PrevioSolicitadoToAprobado extends Transition
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
            PrevioSolicitadoState::class,
            PrevioAprobadoState::class,
            $this->user
        );

        $this->folio->state = PrevioAprobadoState::class;

        if (!$this->folio->previo_code) {
            $this->folio->assignCodeByType();
        }

        $this->folio->save();

        try {
            Mail::to([
                $this->folio->owner,
                ...$this->user->engineering()->get()
            ])
                ->send(new PrevioAprobado($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
