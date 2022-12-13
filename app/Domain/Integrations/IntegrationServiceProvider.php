<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IntegrationRepository::class, function () {
            return $this->app->get(EloquentIntegrationRepository::class);
        });
    }
}
