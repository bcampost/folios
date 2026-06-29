<?php

namespace App\States\Folio;

use App\Models\FolioState;
use Illuminate\Support\Collection;
use Spatie\ModelStates\StateCaster;

class FolioStateCaster extends StateCaster
{
    private string $baseStateClass;

    public function __construct(string $baseStateClass)
    {
        $this->baseStateClass = $baseStateClass;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        // $mapping = $this->getStateMapping();

        // $stateClassName = $mapping[$value];
        // $folioStateModel = $this->getStateModel($stateClassName::getStateId());

        // /** @var \App\States\Folio\FolioState $state */
        // $state = new $stateClassName(
        //     $model,
        //     $folioStateModel->id,
        //     $folioStateModel->title
        // );

        $state = $this->baseStateClass::resolve($value, $model);

        $state->setField($key);

        return $state;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param App\States\Folio\FolioState|string|int $value
     * @param array $attributes
     *
     * @return string
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof $this->baseStateClass) {
            $value->setField($key);
        }

        if (is_int($value)) {
            return $value;
        }

        return $value::getStateId();
    }

    private function getStateMapping(): Collection
    {
        return $this->baseStateClass::getStateMapping();
    }

    private function getStateModel(int $id) : FolioState
    {
        return $this->baseStateClass::getStateModel($id);
    }
}
