<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Mail\NumeroFolioAsignado;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\NumeroFolioAsignadoState;

class FolioAprobadoToNumeroFolioAsignado extends Transition
{
    protected Folio $folio;

    protected User $user;

    protected string $folio_code;

    public function __construct(Folio $folio, User $user, string $folio_code)
    {
        $this->folio = $folio;
        $this->user = $user;
        $this->folio_code = $folio_code;
    }

    public function handle() : Folio
    {
        $this->createTransition(
            FolioAprobadoState::class,
            NumeroFolioAsignadoState::class,
            $this->user
        );

        $this->folio->state = NumeroFolioAsignadoState::class;
        $this->folio->folio_code = $this->folio_code;
        $this->folio->save();

        try {
            Mail::to('test@example.com')->send(new NumeroFolioAsignado($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
