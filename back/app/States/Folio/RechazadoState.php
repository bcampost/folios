<?php

namespace App\States\Folio;

class RechazadoState extends FolioState
{
    public static function getStateId(): int
    {
        return 5;
    }

    public function rules(): array
    {
        return [
            'reason_for_rejection' => ['required', 'string', 'max:255']
        ];
    }
}
