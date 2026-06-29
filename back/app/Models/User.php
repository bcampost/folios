<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleEnum;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'main_user_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_distributor' => 'boolean',
        'email_verified_at' => 'datetime',
        'role' => RoleEnum::class
    ];

    public function assignedUsers() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_user', 'owner_id', 'user_id');
    }

    public function leaders() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_user', 'user_id', 'owner_id');
    }

    public function branches() : BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id');
    }

    public function branch() : BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hasFullAccess() : bool
    {
        return $this->role === RoleEnum::Superadmin || $this->role === RoleEnum::Director;
    }

    public function scopeManagers($query)
    {
        if (!$this->branch_id) {
            return $query->whereRaw('1 = 0');
        }

        $managerIds = User::whereHas('branches', function (Builder $query2) {
            $query2->where('branch_id', $this->branch_id);
        })
        ->pluck('id')
        ->toArray();

        return $query->whereIn('id', $managerIds);
    }

    public function scopeEngineering($query)
    {
        return $query->where('role', RoleEnum::Engineering);
    }

    public function scopeFinance($query)
    {
        return $query->where('role', RoleEnum::Finance);
    }
}
