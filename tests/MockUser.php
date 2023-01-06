<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Auth\CurrentUser;
use App\Domain\Auth\Models\UserModel;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

trait MockUser
{
    private function createMockUser(): CurrentUser
    {
        $userId = 'auth0|' . Uuid::uuid4()->toString();

        $userModel = new UserModel([
            'id' => $userId,
            'name' => 'Jane_Doe',
            'email' => 'jane.doe@test.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        Auth::shouldReceive('user')
            ->andreturn($userModel);

        return new CurrentUser(new Auth());
    }
}
