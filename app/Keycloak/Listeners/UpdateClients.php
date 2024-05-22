<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Events\IntegrationUrlCreated;
use App\Domain\Integrations\Events\IntegrationUrlDeleted;
use App\Domain\Integrations\Events\IntegrationUrlUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Service\ApiClient;
use App\Keycloak\Service\IntegrationToKeycloakClientConverter;
use App\Keycloak\Service\IntegrationUrlConverter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final class UpdateClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly ApiClient $client,
        private readonly ScopeConfig $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationUpdated|IntegrationUrlCreated|IntegrationUrlUpdated|IntegrationUrlDeleted $event): void
    {
        $integration = $this->integrationRepository->getById($event->getIntegrationId());
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationId($event->getIntegrationId());
        $scopeId = $this->scopeConfig->getScopeIdFromIntegrationType($integration);

        foreach ($keycloakClients as $keycloakClient) {
            try {
                $this->updateClient($integration, $keycloakClient, $scopeId);
            } catch (KeyCloakApiFailed $e) {
                $this->failed($event, $e);
            }
        }
    }

    public function failed(IntegrationUpdated|IntegrationUrlCreated|IntegrationUrlUpdated|IntegrationUrlDeleted $integrationUpdated, Throwable $throwable): void
    {
        $this->logger->error('Failed to update Keycloak client(s)', [
            'integration_id' => $integrationUpdated->getIntegrationId()->toString(),
            'exception' => $throwable,
        ]);
    }

    private function updateClient(Integration $integration, Client $keycloakClient, UuidInterface $scopeId): void
    {
        $this->client->updateClient(
            $keycloakClient,
            array_merge(
                IntegrationToKeycloakClientConverter::convert($keycloakClient->id, $integration),
                IntegrationUrlConverter::convert($integration, $keycloakClient)
            )
        );
        $this->client->resetScopes($keycloakClient);
        $this->client->addScopeToClient($keycloakClient->realm, $keycloakClient->id, $scopeId);

        $this->logger->info('Keycloak client updated', [
            'integration_id' => $integration->id->toString(),
            'realm' => $keycloakClient->realm->internalName,
        ]);
    }
}
