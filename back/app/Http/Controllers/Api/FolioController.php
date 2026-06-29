<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedSort;
use App\Http\Resources\FolioResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Requests\Api\Folio\UpdateFolioRequest;
use App\Models\Deal;

class FolioController extends ApiController
{

    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();
        $dealsTable = app(Deal::class)->getTable();
        $productsTable = app(Product::class)->getTable();

        $folios = QueryBuilder::for(Folio::class)
            ->select(
                'folios.*',
                'deals.name as deal_name',
                'products.sku as product_sku',
            )
            ->with('project', 'owner', 'transitions')
            ->leftJoin("{$productsTable} as products", 'products.id', '=', 'folios.reference_product')
            ->leftJoin('projects', 'folios.project_id', '=', 'projects.id')
            ->leftJoin('users', 'projects.owner_id', '=', 'users.id')
            ->leftJoin("{$dealsTable} as deals", 'deals.id', '=', 'projects.deal_id')
            ->when(
                ! auth()->user()->hasFullAccess(),
                function ($query) {
                    $role = auth()->user()->role;

                    if ($role === RoleEnum::Advisor) {
                        $query->where('projects.owner_id', auth()->user()->id);
                    }

                    if ($role === RoleEnum::Admin) {
                        $query->where(function ($query) {
                            // $query->where('users.branch_id', auth()->user()->branch_id)
                            $query->whereIn('users.branch_id', auth()->user()->branches()->pluck('branches.id')->toArray())
                                // ->where('users.role', RoleEnum::Advisor)
                                ->orWhere('projects.owner_id', auth()->user()->id);

                        });
                    }

                    if ($role === RoleEnum::TeamLeader) {
                        $query->where(function ($query) {
                            $query->where('projects.owner_id', auth()->user()->id)
                                ->orWhereIn(
                                    'projects.owner_id',
                                    auth()->user()->assignedUsers()->pluck('users.id')->toArray()
                                );
                        });
                    }

                }
            )
            ->allowedFilters([
                AllowedFilter::exact('classification', 'folios.classification'),
                // AllowedFilter::exact('state', 'folios.state'),
                AllowedFilter::callback('state', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('folios.state', $value);
                    } else {
                        $query->where('folios.state', $value);
                    }
                }),
                AllowedFilter::exact('type'),
                AllowedFilter::callback('search', function ($query, $value) use ($dealsTable) {
                    $query->where(function ($query) use ($value, $dealsTable) {
                        $query->where("{$dealsTable}.name", 'like', '%' . $value . '%')
                            ->orWhere('folios.previo_code', 'like', '%' . $value . '%')
                            ->orWhere('folios.folio_code', 'like', '%' . $value . '%')
                            ->orWhere('users.name', 'like', '%' . $value . '%')
                            ->orWhere('products.sku', 'like', '%' . $value . '%');
                    });
                }),
                AllowedFilter::callback('product_sku', function ($query, $value) {
                    $query->whereHas('product', function ($query) use ($value) {
                        $query->where('sku', 'like', '%' . $value . '%');
                    });
                }),
            ])
            ->allowedSorts([
                'classification',
                AllowedSort::field('cost', 'folios.cost'),
                AllowedSort::field('list_price', 'folios.list_price'),
                AllowedSort::field('updated_at', 'folios.updated_at'),
                AllowedSort::field('created_at', 'folios.created_at'),
                AllowedSort::field('value', 'projects.value'),
                AllowedSort::field('deal_name', 'deals.name'),
                AllowedSort::field('owner', 'users.name'),
            ])
            ->defaultSort('-folios.updated_at', '-folios.created_at');

        $foliosStateCount = ($folios->clone())
            ->selectRaw('count(*) as count')
            ->groupBy('state')
            ->get();

        return FolioResource::collection(($folios->clone()->paginate($limit)))
            ->additional([
                'states_count' => $foliosStateCount->map(function ($folio) {
                    return [
                        'state' => $folio->state->getTitle(),
                        'id' => $folio->state->getId(),
                        'count' => $folio->count
                    ];
                })
            ]);
    }
    public function show(Folio $folio)
    {
        $folio->load(
            'product',
            'category',
            'transitions.user',
            'project.deal',
            'project.owner',
            'project.branch',
            'project.paymentTerm',
            'media',
            'mediaReferenceFiles'
        );

        $folio->assemblySiblings;

        return new FolioResource($folio);
    }

    public function update(UpdateFolioRequest $request, Folio $folio)
    {
        $data = $request->validated();

        if (isset($data['caratula'])) {
            $folio->clearMediaCollection('caratula');
            $folio->addMedia($data['caratula'])->toMediaCollection('caratula');

            unset($data['caratula']);
        }

        if (isset($data['main_reference_image'])) {
            $folio->clearMediaCollection('main_reference_image');
            $folio->addMedia($data['main_reference_image'])->toMediaCollection('main_reference_image');

            unset($data['main_reference_image']);
        }

        if(isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $folio->addMedia($image)->toMediaCollection('previo_image_reference');
            }

            unset($data['images']);
        }

        $folio->update($data);

        return new FolioResource($folio);
    }

    public function destroy(Folio $folio)
    {
        $user = auth()->user();
        $stateId = $folio->state::getStateId();

        if ($stateId === \App\States\Folio\LiberadoParaProduccionState::getStateId()) {
            abort(403, 'No se puede eliminar un folio liberado para producción.');
        }

        $hasFullAccess = $user->hasFullAccess() || $user->role === RoleEnum::Engineering;

        if (! $hasFullAccess) {
            $isOwner = $folio->project?->owner_id === $user->id;
            $isPrevioSolicitado = $stateId === \App\States\Folio\PrevioSolicitadoState::getStateId();

            if (! $isOwner || ! $isPrevioSolicitado) {
                abort(403, 'No tienes permiso para eliminar este registro.');
            }
        }

        $folio->delete();

        return response()->noContent();
    }
}
