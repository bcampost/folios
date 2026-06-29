<?php

namespace App\Http\Requests\Api\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $previos = collect($this->input('previos', []))
            ->map(function ($previo) {
                if (!is_array($previo)) {
                    return $previo;
                }

                foreach ([
                    'is_assembly',
                ] as $field) {
                    if (array_key_exists($field, $previo)) {
                        $previo[$field] = $this->normalizeBooleanValue($previo[$field]);
                    }
                }

                return $previo;
            })
            ->all();

        $this->merge([
            'payment_by_customer_platform' => $this->normalizeBooleanValue($this->input('payment_by_customer_platform')),
            'previos' => $previos,
        ]);
    }

    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string'],
            'channel' => ['required', 'string'],
            'discount' => ['nullable'],
            'deal_id' => ['nullable', 'integer', 'exists:crm.deals,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'payment_term_id' => ['nullable', 'integer', 'exists:payment_terms,id'],
            'modality' => ['nullable', /*'required_unless:payment_term_id,5'*/],
            'negotiated_days' => ['nullable', /*'required_if:payment_term_id,3,4'*/],
            'payment_by_customer_platform' => ['nullable', 'boolean'],
            'previos' => ['required', 'array'],
            'previos.*.width' => ['nullable'],
            'previos.*.height' => ['nullable'],
            'previos.*.depth' => ['nullable'],
            'previos.*.quantity' => ['nullable', 'numeric'],
            'previos.*.melamina_color' => ['required'],
            'previos.*.melamina_density' => ['nullable'],
            'previos.*.chapacinta_color' => ['required'],
            'previos.*.structure_color' => ['required'],
            'previos.*.tela_color' => ['required'],
            'previos.*.package_type' => ['required'],
            'previos.*.classification' => ['required', 'string'],
            'previos.*.title' => ['required', 'string'],
            'previos.*.category_id' => ['nullable', 'integer'],
            'previos.*.description' => ['required', 'string'],
            'previos.*.comments' => ['nullable', 'string'],
            'previos.*.acabados' => ['nullable', 'string'],
            'previos.*.reference_product' => [
                'required_unless:previos.*.classification,D',
                'integer',
                'exists:cotizador.products,id',
            ],
            'previos.*.images' => ['nullable', 'array'],
            'previos.*.images.*' => ['sometimes', 'max:5000'],
            'previos.*.assembly_number' => ['nullable'],
            'previos.*.audio' => ['nullable', 'file', 'mimes:mp3,mp4,m4a,wav,ogg,webm,mpeg', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $previos = collect($this->input('previos', []))
                    ->map(function ($previo, $index) {
                        if (!is_array($previo)) {
                            return $previo;
                        }

                        $previo['images'] = $this->file("previos.{$index}.images", $previo['images'] ?? null);

                        return $previo;
                    })
                    ->all();

                foreach ($previos as $index => $previo) {
                    $classification = $previo['classification'] ?? null;
                    $hasMeasureChange = collect([
                        $previo['width'] ?? null,
                        $previo['height'] ?? null,
                        $previo['depth'] ?? null,
                        $previo['melamina_density'] ?? null,
                    ])->contains(fn ($value) => !in_array($value, [null, ''], true));

                    if (in_array($classification, ['C', 'D'], true)) {
                        if (empty($previo['images']) || !is_array($previo['images'])) {
                            $validator->errors()->add("previos.{$index}.images", 'Debe agregar al menos un archivo de referencia.');
                        }
                    }

                    if ($classification === 'B' && !$hasMeasureChange) {
                        $validator->errors()->add("previos.{$index}.width", 'Debe capturar al menos una medida modificada.');
                    }
                }
            },
        ];
    }

    private function normalizeBooleanValue($value)
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        return $value;
    }
}
