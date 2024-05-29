<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\ClientCollection;
use App\Keycloak\Config;
use App\Keycloak\Events\MissingClientsDetected;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

final class CreateClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly Config $config,
        private readonly ApiClient $client,
        private readonly ScopeConfig $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handleCreateClients(IntegrationCreated $event): void
    {
        $this->handle($event, $this->config->realms);
    }

    public function handleCreatingMissingClients(MissingClientsDetected $event): void
    {
        $missingRealms = $this->keycloakClientRepository->getMissingRealmsByIntegrationId($event->id);

        if (count($missingRealms) === 0) {
            $this->logger->info($event->id . ' - already has all Keycloak clients');
            return;
        }

        $this->handle($event, $missingRealms);
    }


    private function handle(IntegrationCreated|MissingClientsDetected $event, RealmCollection $realms): void
    {
        $clients = $this->createClientsInKeycloak(
            $this->integrationRepository->getById($event->id),
            $realms
        );

        $this->keycloakClientRepository->create(...$clients);

        foreach ($clients as $client) {
            $this->logger->info('Keycloak client created', [
                'integration_id' => $event->id->toString(),
                'client_id' => $client->id->toString(),
                'realm' => $client->realm->internalName,
            ]);
        }
    }

    private function createClientsInKeycloak(Integration $integration, RealmCollection $realms): ClientCollection
    {
        $scopeId = $this->scopeConfig->getScopeIdFromIntegrationType($integration);

        $clientCollection = new ClientCollection();

        foreach ($realms as $realm) {
            try {
                $clientId = Uuid::uuid4();

                $this->client->createClient($realm, $integration, $clientId);
                $client = $this->client->fetchClient($realm, $integration, $clientId);
                $this->client->addScopeToClient($realm, $client->id, $scopeId);

                $clientCollection->add($client);
            } catch (KeyCloakApiFailed $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $clientCollection;
    }

    public function failed(IntegrationCreated|MissingClientsDetected $integrationCreated, Throwable $throwable): void
    {
        $this->logger->error('Failed to create Keycloak clients', [
            'integration_id' => $integrationCreated->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
