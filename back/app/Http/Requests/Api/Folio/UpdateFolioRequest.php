<?php

namespace App\Http\Requests\Api\Folio;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFolioRequest extends FormRequest
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
            'classification' => ['required', 'string'],
            'previo_code' => ['nullable', 'string'],
            'folio_code' => ['nullable', 'string'],
            'caratula' => ['nullable', 'image'],
            'main_reference_image' => ['nullable', 'image'],
            'screw_kits' => ['nullable', 'array'],
            'screw_kits.*.quantity' => ['nullable', 'numeric'],
            'screw_kits.*.description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['sometimes', 'max:5000'],
            'cost' => ['nullable', 'numeric'],
            'list_price' => ['nullable', 'numeric'],
        ];
    }
}
