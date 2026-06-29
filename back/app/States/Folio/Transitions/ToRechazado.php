<?php

namespace App\States\Folio\Transitions;

use App\Enums\FolioTypeEnum;
use App\Models\User;
use App\Models\Folio;
use App\Mail\PrevioRechazado;
use App\States\Folio\RechazadoState;
use Illuminate\Support\Facades\Mail;
use App\States\Folio\PrevioSolicitadoState;

class ToRechazado extends Transition
{
    protected Folio $folio;

    protected User $user;

    protected string $reason_for_rejection;

    public function __construct(Folio $folio, User $user, string $reason_for_rejection)
    {
        $this->folio = $folio;
        $this->user = $user;
        $this->reason_for_rejection = $reason_for_rejection;
    }

    public function handle() : Folio
    {
        if ($this->folio->state instanceof PrevioSolicitadoState) {
            $this->folio->reason_for_rejection = $this->reason_for_rejection;

            try {
                Mail::to($this->folio->owner)->send(new PrevioRechazado($this->folio));
            } catch (\Exception $e) {

            }

            $this->folio->delete();

            if ($this->folio->isAssembly()) {
                $this->folio->assemblySiblings()->delete();
            }

            return $this->folio;
        }

        $this->createTransition(
            get_class($this->folio->state),
            RechazadoState::class,
            $this->user
        );

        $this->folio->state = RechazadoState::class;
        $this->folio->reason_for_rejection = $this->reason_for_rejection;
        $this->folio->save();

        if ($this->folio->isAssembly()) {
            $this->folio->assemblySiblings->each(function (Folio $folio) {
                $folio->transitions()->create([
                    'prev_state_id' => $folio->state::getStateId(),
                    'next_state_id' => RechazadoState::getStateId(),
                    'user_id' => $this->user->id
                ]);

                $folio->state = RechazadoState::class;
                $folio->reason_for_rejection = ($this->folio->type === FolioTypeEnum::Previo ? 'Previo' : 'Folio') .  " #" . $this->folio->id . " rechazado por: " . $this->reason_for_rejection;
                $folio->save();
            });
        }

        try {
            Mail::to($this->folio->owner)->send(new PrevioRechazado($this->folio));
        } catch (\Exception $e) {

        }

        return $this->folio;
    }
}
