<?php

namespace App\Http\Controllers\Api;

use App\Services\BusinessCentralProductMeasuresService;

class ValidateBcSkuController extends ApiController
{
    public function __invoke(BusinessCentralProductMeasuresService $service)
    {
        $sku = trim((string) request('sku', ''));

        if ($sku === '') {
            return $this->responseSuccess([
                'valid' => false,
                'product' => null,
            ]);
        }

        $item = $service->searchBySku($sku);

        if ($item === null) {
            return $this->responseSuccess([
                'valid' => false,
                'product' => null,
            ]);
        }

        return $this->responseSuccess([
            'valid' => true,
            'product' => [
                'sku'          => $item['sku'] ?? $sku,
                'descrpcion'   => $item['descrpcion'] ?? null,
                'description2' => $item['description2'] ?? null,
                'descripcion3' => $item['descripcion3'] ?? null,
                'altura'       => $item['altura'] ?? null,
                'ancho'        => $item['ancho'] ?? null,
                'largo'        => $item['largo'] ?? null,
            ],
        ]);
    }
}
