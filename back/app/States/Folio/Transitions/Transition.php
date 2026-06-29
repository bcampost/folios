<?php

namespace App\States\Folio\Transitions;

use App\Models\User;
use App\Models\Folio;
use App\States\Folio\FolioState;
use Spatie\ModelStates\Transition as BaseTransition;

/**
 * @property App\Models\Folio $folio
 */
class Transition extends BaseTransition
{
    public function createTransition(
        string $from,
        string $to,
        User $user
    )
    {
        $this->folio->transitions()->create([
            'prev_state_id' => $from::getStateId(),
            'next_state_id' => $to::getStateId(),
            'user_id' => $user->id
        ]);
    }
}
