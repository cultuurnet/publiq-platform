<?php

declare(strict_types=1);

namespace App\Auth0\Listeners;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Events\IntegrationUrlCreated;
use App\Domain\Integrations\Events\IntegrationUrlDeleted;
use App\Domain\Integrations\Events\IntegrationUrlUpdated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UpdateClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(
        IntegrationUpdated|IntegrationUrlCreated|IntegrationUrlDeleted|IntegrationUrlUpdated $event
    ): void {
        $integrationId = $event->integrationId ?? $event->id;

        $integration = $this->integrationRepository->getById($integrationId);
        $auth0Clients = $this->auth0ClientRepository->getByIntegrationId($integrationId);

        $this->clusterSDK->updateClientsForIntegration($integration, ...$auth0Clients);

        $this->logger->info(
            'Auth0 client(s) updated',
            [
                'domain' => 'auth0',
                'integration_id' => $integrationId->toString(),
            ]
        );
    }

    public function failed(
        IntegrationUpdated|IntegrationUrlCreated|IntegrationUrlDeleted|IntegrationUrlUpdated $event,
        Throwable $throwable
    ): void {
        $integrationId = $event->integrationId ?? $event->id;

        $this->logger->error('Failed to update Auth0 client(s)', [
            'integration_id' => $integrationId->toString(),
            'exception' => $throwable,
        ]);
    }
}
