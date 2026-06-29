<?php

namespace App\States\Folio;

use ReflectionClass;
use Spatie\ModelStates\State;
use Illuminate\Support\Collection;
use Spatie\ModelStates\Transition;
use Spatie\ModelStates\StateConfig;
use Illuminate\Support\Facades\Cache;
use App\States\Folio\FolioStateCaster;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\DefaultTransition;
use App\Models\FolioState as FolioStateModel;
use App\States\Folio\Transitions\ToRechazado;
use App\Exceptions\FolioStateNotFoundException;
use App\States\Folio\Transitions\FolioSolicitadoToAprobado;
use App\States\Folio\Transitions\PrevioSolicitadoToAprobado;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use App\States\Folio\Transitions\PrevioAprobadoToCostoAsignado;
use App\States\Folio\Transitions\CostoAsignadoToFolioSolicitado;
use App\States\Folio\Transitions\LiberadoParaFacturarToProduccion;
use App\States\Folio\Transitions\FolioAprobadoToNumeroFolioAsignado;
use App\States\Folio\Transitions\FolioAprobadoToLiberadoParaFacturar;
use App\States\Folio\Transitions\CostoAsignadoToPrecioDeListaAsignado;
use App\States\Folio\Transitions\PrecioDeListaAsignadoToFolioSolicitado;

abstract class FolioState extends State
{
    private $model;

    private StateConfig $stateConfig;

    private string $field;

    private static array $stateMapping = [];

    private int $id;

    private string $title;

    public function __construct($model, int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
        $this->stateConfig = static::config();
        $this->model = $model;
        parent::__construct($model);
    }

    abstract public static function getStateId(): int;

    public static function config(): StateConfig
    {
        return parent::config()
            ->allowTransition(
                PrevioSolicitadoState::class,
                PrevioAprobadoState::class,
                PrevioSolicitadoToAprobado::class
            )
            ->allowTransition(
                PrevioAprobadoState::class,
                CostoAsignadoState::class,
                PrevioAprobadoToCostoAsignado::class
            )
            ->allowTransition(
                CostoAsignadoState::class,
                PrecioDeListaAsignadoState::class,
                CostoAsignadoToPrecioDeListaAsignado::class
            )
            ->allowTransition(
                [
                    PrevioSolicitadoState::class,
                    PrevioAprobadoState::class,
                    FolioSolicitadoState::class,
                    FolioAprobadoState::class
                ],
                RechazadoState::class,
                ToRechazado::class
            )
            ->allowTransition(
                CostoAsignadoState::class,
                FolioSolicitadoState::class,
                CostoAsignadoToFolioSolicitado::class
            )
            ->allowTransition(
                PrecioDeListaAsignadoState::class,
                FolioSolicitadoState::class,
                PrecioDeListaAsignadoToFolioSolicitado::class
            )
            ->allowTransition(
                FolioSolicitadoState::class,
                FolioAprobadoState::class,
                FolioSolicitadoToAprobado::class
            )
            ->allowTransition(
                FolioAprobadoState::class,
                NumeroFolioAsignadoState::class,
                FolioAprobadoToNumeroFolioAsignado::class
            )
            ->allowTransition(
                FolioAprobadoState::class,
                LiberadoParaFacturarState::class,
                FolioAprobadoToLiberadoParaFacturar::class
            )
            ->allowTransition(
                LiberadoParaFacturarState::class,
                LiberadoParaProduccionState::class,
                LiberadoParaFacturarToProduccion::class
            );

    }

    public static function castUsing(array $arguments)
    {
        return new FolioStateCaster(static::class);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function rules(): array
    {
        return [];
    }

    public function hasRules(): bool
    {
        return count($this->rules()) > 0;
    }

    public static function getStateMapping(): Collection
    {
        if (! isset(self::$stateMapping[static::class])) {
            self::$stateMapping[static::class] = static::resolveStateMapping();
        }

        return collect(self::$stateMapping[static::class]);
    }

    private static function resolveStateMapping(): array
    {
        $reflection = new ReflectionClass(static::class);

        ['dirname' => $directory] = pathinfo($reflection->getFileName());

        $files = scandir($directory);

        $namespace = $reflection->getNamespaceName();

        $resolvedStates = [];

        $stateConfig = static::config();

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            ['filename' => $className] = pathinfo($file);

            /** @var \Spatie\ModelStates\State|mixed $stateClass */
            $stateClass = $namespace . '\\' . $className;

            if (! is_subclass_of($stateClass, $stateConfig->baseStateClass)) {
                continue;
            }

            $resolvedStates[$stateClass::getStateId()] = $stateClass;
        }

        return $resolvedStates;
    }

    /**
     * @param  string|State  $newState
     * @param  mixed  ...$transitionArgs
     * @return  TModel
     */
    /**
     * @param  string|State  $newState
     * @param  mixed  ...$transitionArgs
     * @return  TModel
     */
    public function transitionTo($newState, ...$transitionArgs)
    {
        $newState = $this->resolveStateObject($newState);

        $from = static::getMorphClass();

        $to = $newState::getMorphClass();

        if (! $this->stateConfig->isTransitionAllowed($from, $to)) {
            throw CouldNotPerformTransition::notFound($from, $to, $this->model);
        }

        $transition = $this->resolveTransitionClass(
            $from,
            $to,
            $newState,
            ...$transitionArgs
        );

        return $this->transition($transition);
    }

    private function resolveStateObject($state): self
    {
        if (is_object($state) && is_subclass_of($state, $this->stateConfig->baseStateClass)) {
            return $state;
        }

        $stateClassName = $this->stateConfig->baseStateClass::resolveStateClass($state);
        $stateModel = $this->stateConfig->baseStateClass::getStateModel($stateClassName::getStateId());

        return new $stateClassName(
            $this->model,
            $stateModel->id,
            $stateModel->title
        );
    }

    private function resolveTransitionClass(
        string $from,
        string $to,
        State $newState,
        ...$transitionArgs
    ): Transition {
        $transitionClass = $this->stateConfig->resolveTransitionClass($from, $to);

        if ($transitionClass === null) {
            $defaultTransition = config('model-states.default_transition', DefaultTransition::class);

            $transition = new $defaultTransition(
                $this->model,
                $this->field,
                $newState
            );
        } else {
            $transition = new $transitionClass($this->model, ...$transitionArgs);
        }

        return $transition;
    }

    public static function getStateModel(int $id) : FolioStateModel
    {
        $modelMapping = self::getStateModelMapping();

        if (!isset($modelMapping[$id])) {
            throw new FolioStateNotFoundException(__('State not found: ') . $id);
        }

        return $modelMapping[$id];
    }

    public static function getStateModelMapping(): Collection
    {
        return Cache::remember('folio_state_mapping', 60, function () {
            return FolioStateModel::all()->keyBy('id');
        });
    }

    public static function resolve(int $id, Model $model = null) : self
    {
        $mapping = static::getStateMapping();
        $stateClassName = $mapping[$id];
        $folioStateModel = static::getStateModel($id);

        $model = $model ?? $folioStateModel;

        $state = new $stateClassName(
            $model,
            $folioStateModel->id,
            $folioStateModel->title
        );

        return $state;
    }
}
