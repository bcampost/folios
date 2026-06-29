<?php

namespace App\Http\Requests\Api\Folio;

use Illuminate\Foundation\Http\FormRequest;

class ReturnToApprovedRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comments' => ['required', 'string']
        ];
    }
}
