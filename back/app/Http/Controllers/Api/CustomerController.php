<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\FolioResource;
use App\Http\Resources\CustomerResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CustomerController extends ApiController
{

    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();

        $products = QueryBuilder::for(Customer::class)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where("name", 'like', '%' . $value . '%');
                    });
                }),
            ])
            ->defaultSort('name');

        return CustomerResource::collection(($products->paginate($limit)));
    }
}
