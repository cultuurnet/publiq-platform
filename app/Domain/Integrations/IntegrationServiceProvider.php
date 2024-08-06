<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Listeners\ActivateIntegration;
use App\Domain\Integrations\Listeners\UpgradeKeyVisibility;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\EloquentIntegrationUrlRepository;
use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\IntegrationUrlRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\KeyVisibilityUpgrades\Events\KeyVisibilityUpgradeCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IntegrationRepository::class, EloquentIntegrationRepository::class);
        $this->app->bind(IntegrationUrlRepository::class, EloquentIntegrationUrlRepository::class);
        $this->app->bind(UdbOrganizerRepository::class, EloquentUdbOrganizerRepository::class);

        Event::listen(IntegrationCreated::class, [ActivateIntegration::class, 'handle']);
        Event::listen(KeyVisibilityUpgradeCreated::class, [UpgradeKeyVisibility::class, 'handle']);
    }
}
