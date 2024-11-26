<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Auth0\Repositories\Auth0ClientRepository;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
use Illuminate\Support\ServiceProvider;

final class Auth0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth0ClientRepository::class, function () {
            return $this->app->get(EloquentAuth0ClientRepository::class);
        });
    }
}
