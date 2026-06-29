<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Mail\FolioAprobado;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\FolioSolicitadoState;

class FolioSolicitadoToAprobado extends Transition
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
            FolioSolicitadoState::class,
            FolioAprobadoState::class,
            $this->user
        );

        $this->folio->state = FolioAprobadoState::class;
        $this->folio->save();

        try {
            Mail::to([
                $this->folio->owner,
                ...$this->user->engineering()->get()
            ])->send(new FolioAprobado($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
