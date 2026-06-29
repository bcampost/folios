<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\FolioResource;
use App\States\Folio\CostoAsignadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\Mail\PrevioFromCostoAsignadoToAprobado;
use App\Http\Requests\Api\Folio\ReturnToApprovedRequest;

class ReturnToApprovedController extends ApiController
{
    public function __invoke(ReturnToApprovedRequest $request, Folio $folio)
    {
        $folio = DB::transaction(function () use($request, $folio) {

            $folio->comments = $request->get('comments');
            $folio->state = PrevioAprobadoState::getStateId();
            $folio->save();

            $folio->transitions()->create([
                'prev_state_id' => CostoAsignadoState::getStateId(),
                'next_state_id' => PrevioAprobadoState::getStateId(),
                'user_id' => $request->user()->id
            ]);

            Mail::to([...$request->user()->engineering()->get()])->send(new PrevioFromCostoAsignadoToAprobado(
                $folio
            ));

            return $folio;
        });

        return new FolioResource($folio);
    }
}
