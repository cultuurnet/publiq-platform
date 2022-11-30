<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Users\Models\UserModel;
use Auth0\Laravel\Auth\Guard;
use Illuminate\Contracts\Auth\Factory;

trait UserAware
{
    public function getUser(): UserModel
    {
        /** @var Factory $auth */
        $auth = auth();

        /** @var Guard $guard */
        $guard = $auth->guard('auth0');

        /** @var UserModel $user */
        $user = $guard->user();

        return $user;
    }
}
