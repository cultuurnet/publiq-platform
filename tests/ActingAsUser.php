<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Ramsey\Uuid\Uuid;

trait ActingAsUser
{
    use InteractsWithAuthentication;

    public function actingAsIntegrator(array $userAttributes = []): self
    {
        return $this->actingAs(UserModel::fromSession([
            'user_id' => Uuid::uuid4()->toString(),
            'email' => 'john.doe@test.com',
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            ...$userAttributes,
        ]));
    }

    public function actingAsAdmin(array $userAttributes = []): self
    {
        return $this->actingAs(UserModel::fromSession([
            'user_id' => Uuid::uuid4()->toString(),
            'email' => 'dev+e2etest-admin@publiq.be',
            'name' => 'Admin The Admin',
            'first_name' => 'Admin',
            'last_name' => 'The Admin',
            ...$userAttributes,
        ]));
    }

    public function actingAsSystemUser(): self
    {
        return $this->actingAs(UserModel::createSystemUser());
    }
}
