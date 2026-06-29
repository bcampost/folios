<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\Lead;
use App\Enums\RoleEnum;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DealResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class DealController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $dealsTable = app(Deal::class)->getTable();
        $customersTable = app(Customer::class)->getTable();
        $leadsTable = app(Lead::class)->getTable();

        $limit = $request->limit ?? $this->getDefaultPageLimit();
        ;

        $deals = QueryBuilder::for (Deal::class)
            ->select([
            'deals.id',
            'deals.customer_id',
            'deals.lead_id',
            'deals.name',
            'deals.type',
            'deals.quote_id',
            'deals.value',
        ])
            ->with(['customer', 'lead'])
            ->leftJoin($customersTable, 'deals.customer_id', '=', $customersTable . '.id')
            ->leftJoin($leadsTable, 'deals.lead_id', '=', $leadsTable . '.id')
            ->allowedFilters([
            AllowedFilter::callback('search', function (Builder $query, $value) use ($customersTable, $leadsTable) {
            $query->where(function ($query) use ($value, $customersTable, $leadsTable) {
                    $query->where('deals.name', 'like', "%$value%")
                        ->orWhere($customersTable . '.name', 'like', "%$value%")
                        ->orWhere($customersTable . '.company_name', 'like', "%$value%")
                        ->orWhere($leadsTable . '.name', 'like', "%$value%")
                        ->orWhere($leadsTable . '.company_name', 'like', "%$value%");
                }
                );
            })
        ])
            ->whereIn('deals.status', ['en proceso', 'nuevo', 'ganado'])
            ->defaultSort('deals.name');

        $deals->when(
            !auth()->user()->hasFullAccess(),
                function ($query) {
            if (auth()->user()->role == RoleEnum::Admin->value) {
                $userIds = DB::connection('crm')
                    ->table('users')
                    ->where('role', RoleEnum::Advisor->value)
                    ->where('branch', auth()->user()->branch)
                    ->pluck('id')
                    ->toArray();

                $query->whereIn('deals.owner_id', $userIds);

            }
            elseif (auth()->user()->role == RoleEnum::Advisor->value) {
                $query->where('deals.owner_id', auth()->user()->crm_id);
            }
            else {
                $query->where('deals.owner_id', auth()->user()->crm_id);
            }
        });

        return DealResource::collection(($deals->paginate($limit)));
    }

    /**
     * Display the specified resource.
     */
    public function show(Deal $deal)
    {
        // Apply the same authorization logic as the index method
        if (!auth()->user()->hasFullAccess()) {
            if (auth()->user()->role == RoleEnum::Admin->value) {
                $userIds = DB::connection('crm')
                    ->table('users')
                    ->where('role', RoleEnum::Advisor->value)
                    ->where('branch', auth()->user()->branch)
                    ->pluck('id')
                    ->toArray();

                if (!in_array($deal->owner_id, $userIds)) {
                    abort(403, 'Unauthorized');
                }
            }
            elseif (auth()->user()->role == RoleEnum::Advisor->value) {
                if ($deal->owner_id !== auth()->user()->crm_id) {
                    abort(403, 'Unauthorized');
                }
            }
            else {
                if ($deal->owner_id !== auth()->user()->crm_id) {
                    abort(403, 'Unauthorized');
                }
            }
        }

        // Only return deals with appropriate status
        if (!in_array($deal->status, ['en proceso', 'nuevo', 'ganado'])) {
            abort(404, 'Deal not found or not accessible');
        }

        return new DealResource($deal->load(['customer', 'lead']));
    }
}
