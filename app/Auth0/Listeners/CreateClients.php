<?php

declare(strict_types=1);

namespace App\Auth0\Listeners;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CreateClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Auth0ClusterSDK $auth0ClusterSDK,
        private readonly IntegrationRepository $integrationRepository,
        private readonly Auth0ClientRepository $auth0ClientRepository
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $integration = $this->integrationRepository->getById($integrationCreated->id);
        $auth0Clients = $this->auth0ClusterSDK->createClientsForIntegration($integration);
        $this->auth0ClientRepository->save(...$auth0Clients->getAllClients());
    }
}
