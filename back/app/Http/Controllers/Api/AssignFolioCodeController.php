<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use App\Models\FolioCodeSequence;
use App\Http\Resources\FolioResource;
use App\Http\Requests\Api\Folio\AssignFolioCodeRequest;

class AssignFolioCodeController extends ApiController
{
    public function __invoke(AssignFolioCodeRequest $request, Folio $folio)
    {
        if ($request->filled('folio_code')) {
            $folio->folio_code = $request->input('folio_code');
        } else {
            $folio->code_number = FolioCodeSequence::getNextNumber('folio');
            $folio->folio_code = $folio->generateFolioCode();
        }

        $folio->save();

        return new FolioResource($folio);
    }
}
