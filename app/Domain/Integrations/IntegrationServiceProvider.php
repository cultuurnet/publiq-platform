<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Integrations\Jobs\DistributeKeys;
use App\Domain\Integrations\Jobs\DistributeKeysHandler;
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

        Event::listen(DistributeKeys::class, [DistributeKeysHandler::class, 'handle']);
    }
}
