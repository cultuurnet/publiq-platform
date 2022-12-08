<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    protected function gate(): void
    {
        Gate::define(
            'viewHorizon',
            static fn ($user) => in_array($user->email, config('horizon.users'), true)
        );
    }
}
