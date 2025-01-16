<?php

declare(strict_types=1);

namespace App\Providers;

use App\Nova\Dashboards\Main;
use App\Nova\Resources\Integration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Laravel\Nova\Dashboard;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

final class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Schema::morphUsingUuids();

        Nova::mainMenu(
            fn () => array_map(fn ($resource) => MenuItem::resource($resource), Nova::$resources)
        );
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
            static fn ($user) => in_array($user->email, config('nova.users'), true)
        );
    }

    protected function resources(): void
    {
        Nova::resourcesIn(app_path('Nova/Resources'));
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
        parent::register();
        Nova::initialPath('/resources/' . Integration::uriKey());
    }
}
