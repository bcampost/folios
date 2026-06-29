<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ActivityLogResource;
use App\Models\Folio;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FolioActivityController extends ApiController
{
    public function __invoke(Request $request, Folio $folio)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();

        $activities = QueryBuilder::for($folio->activities())
            ->with('causer')
            ->defaultSort('-created_at');

        return ActivityLogResource::collection($activities->paginate($limit));
    }
}
