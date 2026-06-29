<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    private $pageLimit = 20;

    public function responseNoContent(): Response
    {
        return response()->noContent();
    }

    public function responseSuccess($data = [], $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    public function getDefaultPageLimit() : int
    {
        return $this->pageLimit;
    }
}
