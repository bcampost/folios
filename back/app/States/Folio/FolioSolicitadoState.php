<?php

namespace App\States\Folio;

class FolioSolicitadoState extends FolioState
{
    public static function getStateId(): int
    {
        return 6;
    }

    public function rules(): array
    {
        return [
            'melamina_color'   => ['required', 'not_in:POR DEFINIR'],
            'chapacinta_color' => ['required', 'not_in:POR DEFINIR'],
            'structure_color'  => ['required', 'not_in:POR DEFINIR'],
            'tela_color'       => ['required', 'not_in:POR DEFINIR'],
            'acabados'         => ['nullable', 'string'],
        ];
    }
}
