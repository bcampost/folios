<?php

namespace App\Http\Requests\Api\Folio;

use App\States\Folio\FolioAprobadoState;
use Illuminate\Foundation\Http\FormRequest;

class AssignExistingFolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $folio = $this->route('folio');

        if ($folio->folio_code) {
            return false;
        }

        if ($folio->state::getStateId() !== FolioAprobadoState::getStateId()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:50'],
        ];
    }
}
