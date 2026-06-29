<?php

namespace App\Http\Requests\Api\Folio;

use App\Models\Folio;
use Illuminate\Support\Arr;
use App\States\Folio\FolioState;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFolioStateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'state_id' => ['required', 'exists:folio_states,id'],
        ];
    }

    public function getState(): FolioState
    {
        return FolioState::resolve($this->state_id);
    }

    public function getStateParams(): array
    {
        return $this->only(
            array_keys(
                $this->getState()->rules()
            )
        );
    }
}
