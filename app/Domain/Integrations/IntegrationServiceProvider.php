<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use App\Domain\Integrations\Listeners\DistributeKeys;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\EloquentIntegrationUrlRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\IntegrationUrlRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IntegrationRepository::class, EloquentIntegrationRepository::class);
        $this->app->bind(IntegrationUrlRepository::class, EloquentIntegrationUrlRepository::class);

        Event::listen(IntegrationActivatedWithCoupon::class, [DistributeKeys::class, 'handle']);
        Event::listen(IntegrationActivatedWithOrganization::class, [DistributeKeys::class, 'handle']);
    }
}
