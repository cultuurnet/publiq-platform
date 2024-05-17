<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\ClientCollection;
use App\Keycloak\Config;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Service\ApiClient;
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
        private readonly ApiClient $client,
        private readonly Config $config,
        private readonly ScopeConfig $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $clients = $this->createClients($this->integrationRepository->getById($integrationCreated->id));

        $this->keycloakClientRepository->create(...$clients);

        foreach ($clients as $client) {
            $this->logger->info('Keycloak client created', [
                'integration_id' => $integrationCreated->id->toString(),
                'realm' => $client->realm->internalName,
            ]);
        }
    }

    public function failed(IntegrationCreated $integrationCreated, Throwable $throwable): void
    {
        $this->logger->error('Failed to create Keycloak client(s)', [
            'integration_id' => $integrationCreated->id->toString(),
            'exception' => $throwable,
        ]);
    }

    private function createClients(Integration $integration): ClientCollection
    {
        $scopeId = $this->scopeConfig->getScopeIdFromIntegrationType($integration);

        $clientCollection = new ClientCollection();

        foreach ($this->config->realms as $realm) {
            try {
                $clientId = $this->client->createClient($realm, $integration);
                $this->client->addScopeToClient($realm, $clientId, $scopeId);

                $client = $this->client->fetchClient($realm, $integration);
                $clientCollection->add($client);
            } catch (KeyCloakApiFailed $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $clientCollection;
    }
}
