<?php

namespace App\States\Folio;

class CostoAsignadoState extends FolioState
{
    public static function getStateId(): int
    {
        return 3;
    }

    public function rules(): array
    {
        return [
            'cost'         => ['required', 'numeric'],
            // 'list_price'   => ['required', 'numeric'],
            'cost_details' => ['nullable', 'string']
        ];
    }
}
