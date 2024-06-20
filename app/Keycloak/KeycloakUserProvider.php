<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Session\SessionManager;

final class KeycloakUserProvider implements UserProviderContract
{
    public function __construct(private readonly SessionManager $session)
    {
    }

    public function retrieveById($identifier): UserModel
    {
        return UserModel::fromSession($this->session->get('user'));
    }

    public function retrieveByToken($identifier, $token)
    {
        throw new \Exception('Not implemented');
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new \Exception('Not implemented');
    }

    public function retrieveByCredentials(array $credentials)
    {
        throw new \Exception('Not implemented');
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new \Exception('Not implemented');
    }
}
