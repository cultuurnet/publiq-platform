<?php

declare(strict_types=1);

namespace App\Auth0\Jobs;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateMissingClientsHandler implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Auth0ClusterSDK $auth0ClusterSDK,
        private readonly IntegrationRepository $integrationRepository,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(CreateMissingClients $createMissingAuth0Clients): void
    {
        $missingTenants = $this->auth0ClientRepository->getMissingTenantsByIntegrationId($createMissingAuth0Clients->id);

        if (count($missingTenants) === 0) {
            $this->logger->info($createMissingAuth0Clients->id . ' - already has all Auth0 clients');
            return;
        }

        $integration = $this->integrationRepository->getById($createMissingAuth0Clients->id);
        foreach ($missingTenants as $missingTenant) {
            $auth0Client = $this->auth0ClusterSDK->createClientForIntegrationOnAuth0Tenant($integration, $missingTenant);
            $this->auth0ClientRepository->save($auth0Client);
            $this->logger->info($integration->id . ' - created Auth0 client on ' . $missingTenant->value);
        }
    }

    public function failed(CreateMissingClients $createMissingAuth0Clients, Throwable $throwable): void
    {
        $this->logger->error('Failed to create missing Auth0 client(s)', [
            'integration_id' => $createMissingAuth0Clients->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
