<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Environments;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\ClientCollection;
use App\Keycloak\ClientId\ClientIdUuidStrategy;
use App\Keycloak\Events\MissingClientsDetected;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realms;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly Realms $realms,
        private readonly ApiClient $client,
        private readonly ScopeConfig $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handleCreateClients(IntegrationCreated $event): void
    {
        $this->handle($event, $this->realms);
    }

    public function handleCreatingMissingClients(MissingClientsDetected $event): void
    {
        $missingEnvironments = $this->keycloakClientRepository->getMissingEnvironmentsByIntegrationId($event->id);

        if (count($missingEnvironments) === 0) {
            $this->logger->info($event->id . ' - already has all Keycloak clients');
            return;
        }

        $this->handle(
            $event,
            $this->convertMissingEnvironmentsToMissingRealms($missingEnvironments)
        );
    }

    private function handle(IntegrationCreated|MissingClientsDetected $event, Realms $realms): void
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
                'realm' => $client->getRealm()->internalName,
            ]);
        }
    }

    private function createClientsInKeycloak(Integration $integration, Realms $realms): ClientCollection
    {
        $scopeId = $this->scopeConfig->getScopeIdFromIntegrationType($integration);

        $clientCollection = new ClientCollection();

        foreach ($realms as $realm) {
            try {
                $client = $this->client->createClient($realm, $integration, new ClientIdUuidStrategy());
                $this->client->addScopeToClient($client, $scopeId);

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

    private function convertMissingEnvironmentsToMissingRealms(Environments $missingEnvironments): Realms
    {
        $missingEnvValues = array_column($missingEnvironments->toArray(), 'value');

        $missingRealms = new Realms();
        foreach ($this->realms as $realm) {
            if (in_array($realm->environment->value, $missingEnvValues, true)) {
                $missingRealms->add($realm);
            }
        }

        return $missingRealms;
    }
}
