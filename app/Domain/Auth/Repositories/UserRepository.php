<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Models\UserModel;
use Auth0\Laravel\Contract\Auth\User\Repository;
use Illuminate\Contracts\Auth\Authenticatable;

final class UserRepository implements Repository
{
    /**
     * @param array<string> $user
     */
    public function fromSession(array $user): ?Authenticatable
    {
        return new UserModel([
            'id' => $user['sub'] ?? $user['user_id'] ?? null,
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }

    /**
     * @param array<string> $user
     */
    public function fromAccessToken(array $user): ?Authenticatable
    {
        return null;
    }
}
