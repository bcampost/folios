<?php

namespace App\States\Folio;

use App\Exceptions\FolioStateNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\FolioState as FolioStateModel;

class FolioStateFactory
{
    public static function make(int $id, Model $model)
    {
        $mapping = FolioState::getStateMapping();
        $stateClassName = $mapping[$id];

        $folioStateModel = self::getStateModel($id);

    }

    public static function getStateModel(int $id) : FolioStateModel
    {
        $modelMapping = self::getStateModelMapping();

        if (!isset($modelMapping[$id])) {
            throw new FolioStateNotFoundException('State not found: ' . $id);
        }

        return $modelMapping[$id];
    }

    public static function getStateModelMapping(): Collection
    {
        return Cache::remember('folio_state_mapping', 60, function () {
            return FolioStateModel::all()->keyBy('id');
        });
    }
}
