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

        $this->app->when(Login::class)
            ->needs('$loginParams')
            ->give(config('auth.login_parameters'));
    }
}
