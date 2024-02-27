<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Jobs;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class DistributeKeysHandler implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly IntegrationRepository     $integrationRepository,
        private readonly Auth0ClientRepository     $auth0ClientRepository,
        private readonly UiTiDv1ConsumerRepository $uiTiDv1ConsumerRepository,
    ) {
    }

    public function handle(DistributeKeys $job): void
    {
        $integration = $this->integrationRepository->getById($job->id);
        $clients = array_filter($integration->auth0Clients(), fn (Auth0Client $client) => $client->tenant === Auth0Tenant::Production);
        $consumers = array_filter($integration->uiTiDv1Consumers(), fn (UiTiDv1Consumer $consumer) => $consumer->environment === UiTiDv1Environment::Production);

        $this->auth0ClientRepository->distribute(...$clients);
        $this->uiTiDv1ConsumerRepository->distribute(...$consumers);
    }
}
