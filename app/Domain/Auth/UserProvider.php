<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Models\UserModel;
use Auth0\SDK\Auth0;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;

final class UserProvider implements UserProviderContract
{
    public function retrieveById($identifier)
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        $user = $auth0->getUser();
        if ($user) {
            return UserModel::fromSession($user);
        }
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

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        throw new \Exception('Not implemented');
    }
}
