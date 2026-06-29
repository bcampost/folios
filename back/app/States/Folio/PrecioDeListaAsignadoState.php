<?php

namespace App\States\Folio;

class PrecioDeListaAsignadoState extends FolioState
{
    public static function getStateId(): int
    {
        return 4;
    }

    public function rules(): array
    {
        return [
            'list_price' => ['required', 'numeric'],
            'list_price_details' => ['nullable', 'string']
        ];
    }
}
