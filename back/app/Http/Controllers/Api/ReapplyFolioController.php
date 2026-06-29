<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use App\Enums\RoleEnum;
use App\Mail\FolioAprobado;
use App\Mail\PrevioAprobado;
use Illuminate\Http\Request;
use App\Mail\FolioSolicitado;
use App\Mail\PrevioSolicitado;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\FolioResource;
use App\Enums\FolioClassificationEnum;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\Http\Requests\Api\Folio\ReapplyFolioRequest;

class ReapplyFolioController extends ApiController
{
    public function __invoke(ReapplyFolioRequest $request, Folio $folio)
    {
        // clone folio with the attributes from the request
        $attributes = $request->validated();
        $newFolio = $folio->replicate()->fill($attributes);

        // $isApprovedState = (
        //     $request->user()->role !== RoleEnum::Advisor ||
        //     in_array(
        //         $request->classification, [
        //             FolioClassificationEnum::A->value
        //         ]
        //     )
        // ) ? true : false;

        // $newFolio->state = !$isApprovedState ? PrevioSolicitadoState::getStateId() : PrevioAprobadoState::getStateId();
        $state = $this->getFolioState($request->classification);

        $newFolio->state = $state;

        $newFolio->save();
        $newFolio->refresh();

        if($folio->media()->count() > 0) {
            $folio->media()->each(function($media) use($newFolio) {
                $newFolio->addMedia($media->getPath())->toMediaCollection('previo_image_reference');
            });
        }

        $folio->transitions()->delete();

        $folio->delete();



        if ($state !== PrevioSolicitadoState::getStateId()) {
            $newFolio->transitions()->create([
                'prev_state_id' => PrevioSolicitadoState::getStateId(),
                'next_state_id' => $state,
                'user_id' => $request->user()->id
            ]);
        }


        if($newFolio->state instanceof PrevioSolicitadoState) {
            if ($request->user()->role === RoleEnum::Advisor) {
                try {
                    Mail::to([
                        ...$newFolio->owner->managers()->get(),
                        ...$newFolio->owner->leaders,
                    ])->send(new PrevioSolicitado($newFolio->project, $newFolio));
                } catch (\Exception $e) {

                }
            }
        }elseif ($newFolio->state instanceof PrevioAprobadoState) {
            try {
                Mail::to([...$newFolio->owner->engineering()->get()])->send(new PrevioAprobado(
                    $newFolio
                ));
            } catch (\Exception $e) {
                throw $e;
            }
        }elseif ($newFolio->state instanceof FolioAprobadoState) {
            try {
                Mail::to([...$newFolio->owner->engineering()->get()])->send(new FolioAprobado(
                    $newFolio
                ));
            } catch (\Exception $e) {
                throw $e;
            }
        }elseif ($newFolio->state instanceof FolioSolicitadoState) {
            try {
                Mail::to([
                    ...$newFolio->owner->managers()->get(),
                    ...$newFolio->owner->leaders,
                ])->send(new FolioSolicitado($newFolio));
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return new FolioResource($newFolio);
    }

    private function getFolioState(string $classification)
    {
        $state = PrevioSolicitadoState::getStateId();

        if(auth()->user()->role !== RoleEnum::Advisor) {
            $state = $classification === FolioClassificationEnum::A->value ? FolioAprobadoState::getStateId() : PrevioAprobadoState::getStateId();
        } else {
            $state = $classification === FolioClassificationEnum::A->value ? FolioSolicitadoState::getStateId() : PrevioSolicitadoState::getStateId();
        }

        return $state;
    }

}
