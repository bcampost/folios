<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\Enums\FolioTypeEnum;
use App\Mail\FolioSolicitado;
use App\Mail\PrecioListaAsignado;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\FolioSolicitadoState;

class CostoAsignadoToFolioSolicitado extends Transition
{
    protected Folio $folio;

    protected User $user;

    protected string $melamina_color;

    protected string $chapacinta_color;

    protected string $structure_color;

    protected string $tela_color;

    protected ?string $acabados;

    public function __construct(
        Folio $folio,
        User $user,
        string $melamina_color,
        string $chapacinta_color,
        string $structure_color,
        string $tela_color,
        ?string $acabados
    )
    {
        $this->folio = $folio;
        $this->user = $user;
        $this->melamina_color = $melamina_color;
        $this->chapacinta_color = $chapacinta_color;
        $this->structure_color = $structure_color;
        $this->tela_color = $tela_color;
        $this->acabados = $acabados;
    }

    public function handle() : Folio
    {
        $this->createTransition(
            CostoAsignadoState::class,
            FolioSolicitadoState::class,
            $this->user
        );

        $this->folio->state = FolioSolicitadoState::class;
        $this->folio->type = FolioTypeEnum::Folio;
        $this->folio->melamina_color = $this->melamina_color;
        $this->folio->chapacinta_color = $this->chapacinta_color;
        $this->folio->structure_color = $this->structure_color;
        $this->folio->tela_color = $this->tela_color;
        $this->folio->acabados = $this->acabados ? $this->acabados : $this->folio->acabados;
        $this->folio->save();

        try {
            Mail::to($this->folio->owner->managers()->get())->send(new FolioSolicitado($this->folio));
        } catch (\Exception $e) {
            //
        }

        return $this->folio;
    }
}
