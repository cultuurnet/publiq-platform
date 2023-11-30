<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Controllers\AccessController;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(AccessController::class)
            ->needs('$adminEmails')
            ->give(config('nova.users'));
    }
}
