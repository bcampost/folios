<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return CategoryResource::collection($categories);
    }
}
