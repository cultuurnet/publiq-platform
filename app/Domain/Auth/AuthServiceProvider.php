<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Controllers\AccessController;
use Auth0\SDK\Auth0;
use Auth0\SDK\Contract\Auth0Interface;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Auth0Interface::class, Auth0::class);

        $this->app->when(AccessController::class)
            ->needs('$adminEmails')
            ->give(config('nova.users'));
    }
}
