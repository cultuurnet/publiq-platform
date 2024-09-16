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
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Converters\IntegrationToKeycloakClientConverter;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Exception\RealmNotAvailable;
use App\Keycloak\Realms;
use App\Keycloak\Repositories\KeycloakClientRepository;
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
        private readonly Realms $realms,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationUpdated|IntegrationUrlCreated|IntegrationUrlUpdated|IntegrationUrlDeleted $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationId($event->id);

        foreach ($keycloakClients as $keycloakClient) {
            try {
                $realm = $this->realms->getRealmByEnvironment($keycloakClient->environment);

                $this->updateClient(
                    $integration,
                    $keycloakClient,
                    $realm->scopeConfig->getScopeIdsFromIntegrationType($integration)
                );
            } catch (KeyCloakApiFailed|RealmNotAvailable $e) {
                $this->failed($event, $e);
            }
        }
    }

    public function failed(IntegrationUpdated|IntegrationUrlCreated|IntegrationUrlUpdated|IntegrationUrlDeleted $integrationUpdated, Throwable $throwable): void
    {
        $this->logger->error('Failed to update Keycloak client(s)', [
            'integration_id' => $integrationUpdated->id->toString(),
            'exception' => $throwable,
        ]);
    }

    /**
     * @param UuidInterface[] $scopeIds
     */
    private function updateClient(Integration $integration, Client $keycloakClient, array $scopeIds): void
    {
        $this->client->updateClient(
            $keycloakClient,
            IntegrationToKeycloakClientConverter::convert(
                $keycloakClient->id,
                $integration,
                $keycloakClient->clientId,
                $keycloakClient->environment
            )
        );
        $this->client->deleteScopes($keycloakClient);
        foreach ($scopeIds as $scopeId) {
            $this->client->addScopeToClient($keycloakClient, $scopeId);
        }

        $this->logger->info('Keycloak client updated', [
            'integration_id' => $integration->id->toString(),
            'client_id' => $keycloakClient->clientId,
            'environment' => $keycloakClient->environment->value,
        ]);
    }
}
