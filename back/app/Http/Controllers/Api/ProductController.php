<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\BusinessCentralProductMeasuresService;
use App\Http\Resources\ProductResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ProductController extends ApiController
{

    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();

        $products = QueryBuilder::for(Product::class)
            ->with('category')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where("title", 'like', '%' . $value . '%')
                            ->orWhere("sku", 'like', '%' . $value . '%');
                    });
                }),
                AllowedFilter::callback('without_owner', function ($query, $value) {
                    if($value == 1) {
                        $query->whereNull('owner_id');
                    }
                })
            ])
            ->defaultSort('title');

        return ProductResource::collection(($products->paginate($limit)));
    }

    public function measures(Product $product, BusinessCentralProductMeasuresService $businessCentralProductMeasuresService)
    {
        $measures = $businessCentralProductMeasuresService->getMeasuresBySku($product->sku);

        return $this->responseSuccess([
            'data' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'width' => Arr::get($measures, 'width', $product->width) ?? $product->width,
                'height' => Arr::get($measures, 'height', $product->height) ?? $product->height,
                'depth' => Arr::get($measures, 'depth', $product->depth) ?? $product->depth,
                'melamina_density' => Arr::get($measures, 'melamina_density', $product->melamina_density) ?? $product->melamina_density,
            ],
        ]);
    }
}
