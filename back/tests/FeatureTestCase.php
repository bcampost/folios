<?php

namespace Tests;

use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class FeatureTestCase extends BaseTestCase
{
    use CreatesApplication;
    use LazilyRefreshDatabase;

    protected User $currentUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function signIn(User $user = null, bool $api = false): self
    {
        if (! $user) {
            $user = User::factory()->create();
        }

        $this->currentUser = $user;

        return $this->actingAs($user, $api ? 'api' : 'web');
    }

    protected function asRole(RoleEnum $role = null, User $user = null, bool $api = false): self
    {
        $this->signIn($user, $api);

        if ($role) {
            $this->getUser()->role = $role->value;
            $this->getUser()->save();
            // $this->getUser()->assignRole($role->value);
        }

        return $this;
    }

    protected function asAdmin(User $user = null, bool $api = false): self
    {
        return $this->asRole(RoleEnum::Admin, $user, $api);
    }

    protected function asSuperAdmin(User $user = null, bool $api = false): self
    {
        return $this->asRole(RoleEnum::Superadmin, $user, $api);
    }

    public function getUser(): User
    {
        return $this->currentUser;
    }
}
