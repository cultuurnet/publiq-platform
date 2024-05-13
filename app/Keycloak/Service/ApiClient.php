<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Json;
use App\Keycloak\Client\KeycloakClientWithBearer;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use Exception;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class ApiClient
{
    public function __construct(
        private KeycloakClientWithBearer $client,
        private LoggerInterface $logger
    ) {
    }

    public function createClient(Realm $realm, Integration $integration): UuidInterface
    {
        $id = Uuid::uuid4();

        try {
            $response = $this->client->send(
                new Request(
                    'POST',
                    sprintf('admin/realms/%s/clients', $realm->internalName),
                    [],
                    Json::encode(IntegrationToKeycloakClientConverter::convert($id, $integration))
                )
            );
        } catch (Exception $e) {
            throw KeyCloakApiFailed::failedToCreateClient($e->getMessage());
        }

        if ($response->getStatusCode() !== 201) {
            throw KeyCloakApiFailed::failedToCreateClientWithResponse($response);
        }

        $this->logger->info(sprintf('Client %s, client id %s created with id %s', $integration->name, $integration->id->toString(), $id->toString()));

        return $id;
    }
}
