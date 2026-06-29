<?php

namespace App\States\Folio;

class NumeroFolioAsignadoState extends FolioState
{
    public static function getStateId(): int
    {
        return 8;
    }

    public function rules(): array
    {
        return [
            'folio_code' => ['required', 'string']
        ];
    }
}
