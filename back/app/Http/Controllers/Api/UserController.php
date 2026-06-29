<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedSort;

class UserController extends ApiController
{
    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();;

        $users = QueryBuilder::for(User::class)
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->allowedFilters([
            ])
            ->allowedSorts([
                'name',
                'email',
                'created_at',
                AllowedSort::field('branch', 'branches.name'),
            ])
            ->allowedIncludes(['branch'])
            ->defaultSort('name')
            ->where('users.id', '<>', auth()->user()->id);

        return UserResource::collection(($users->paginate($limit)));
    }
}
