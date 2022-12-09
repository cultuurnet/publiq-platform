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

        $auth0LoginParameters = [];
        parse_str(config('auth0.login_parameters'), $auth0LoginParameters);

        $this->app->when(Login::class)
            ->needs('$loginParams')
            ->give($auth0LoginParameters);
    }
}
