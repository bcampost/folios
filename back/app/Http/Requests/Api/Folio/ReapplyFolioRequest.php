<?php

namespace App\Http\Requests\Api\Folio;

use Illuminate\Foundation\Http\FormRequest;

class ReapplyFolioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'width' => ['nullable'],
            'height' => ['nullable'],
            'depth' => ['nullable'],
            'quantity' => ['required', 'numeric'],
            'melamina_color' => ['required'],
            'melamina_density' => ['nullable'],
            'chapacinta_color' => ['required'],
            'structure_color' => ['required'],
            'tela_color' => ['required'],
            'package_type' => ['required'],
            'title' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer'],
            'description' => ['required', 'string'],
            'comments' => ['nullable', 'string'],
            'acabados' => ['nullable', 'string'],
            'classification' => ['required', 'string']
        ];
    }
}
