<?php

declare(strict_types=1);

namespace App\Providers;

use App\Nova\Dashboards\Main;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Laravel\Nova\Dashboard;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

final class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Schema::morphUsingUuids();
    }

    protected function routes(): void
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    protected function gate(): void
    {
        Gate::define(
            'viewNova',
            fn ($user) => in_array($user->email, Config::get('nova.users'), true)
        );
    }

    /**
     * @return array<Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            new Main(),
        ];
    }

    public function register(): void
    {
    }
}
