<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Keycloak\ClientCollection;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\RealmCollection;
use App\Keycloak\ScopeConfig;
use Psr\Log\LoggerInterface;

final readonly class CreateClientForRealms
{
    public function __construct(
        private ApiClient $client,
        private ScopeConfig $scopeConfig,
        private LoggerInterface $logger
    ) {
    }

    public function createInKeycloak(Integration $integration, RealmCollection $realms): ClientCollection
    {
        $scopeId = $this->scopeConfig->getScopeIdFromIntegrationType($integration);

        $clientCollection = new ClientCollection();

        foreach ($realms as $realm) {
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
