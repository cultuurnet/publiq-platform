<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Auth\Controllers\Login;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        $loginParameters = [];
        parse_str(config('auth.auth0_login_parameters', ''), $loginParameters);

        $this->app->when(Login::class)
            ->needs('$loginParams')
            ->give($loginParameters);
    }
}
