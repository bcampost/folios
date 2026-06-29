<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use App\Http\Requests\Api\Folio\UpdateFolioStateRequest;
use App\Http\Resources\FolioResource;

class UpdateFolioStateController extends ApiController
{
    public function __invoke(UpdateFolioStateRequest $request, Folio $folio)
    {
        $state = $request->getState();

        if($state->hasRules()) {
            $request->validate($state->rules());
        }

        $folio = $folio->state->transitionTo(
            $state,
            $request->user(),
            ...$request->getStateParams()
        );

        return new FolioResource($folio);
    }
}
