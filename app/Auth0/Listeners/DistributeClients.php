<?php

declare(strict_types=1);

namespace App\Auth0\Listeners;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class DistributeClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly Auth0ClientRepository $auth0ClientRepository
    ) {
    }

    public function handle(IntegrationActivated $integrationActivated): void
    {
        $integration = $this->integrationRepository->getById($integrationActivated->id);
        $clients = array_filter($integration->auth0Clients(), fn (Auth0Client $client) => $client->tenant === Auth0Tenant::Production);

        $this->auth0ClientRepository->distribute(...$clients);
    }
}
