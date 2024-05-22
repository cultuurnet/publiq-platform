<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\ApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class EnableClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly ApiClient $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationActivated $integrationActivated): void
    {
        $integration = $this->integrationRepository->getById($integrationActivated->id);
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationId($integrationActivated->id);

        foreach ($keycloakClients as $keycloakClient) {
            try {
                $this->client->enableClient($keycloakClient);

                $this->logger->info('Keycloak client enabled', [
                    'integration_id' => $integration->id->toString(),
                    'client_id' => $keycloakClient->id->toString(),
                    'realm' => $keycloakClient->realm->internalName,
                ]);
            } catch (KeyCloakApiFailed $e) {
                $this->failed($integrationActivated, $e);
            }
        }
    }

    public function failed(IntegrationActivated $integrationActivated, Throwable $throwable): void
    {
        $this->logger->error('Failed to enable Keycloak client(s)', [
            'integration_id' => $integrationActivated->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
