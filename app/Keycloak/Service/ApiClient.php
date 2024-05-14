<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Json;
use App\Keycloak\Client;
use App\Keycloak\Client\KeycloakHttpClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class ApiClient
{
    public function __construct(
        private KeycloakHttpClient $client,
        private LoggerInterface $logger
    ) {
    }

    public function createClient(Realm $realm, Integration $integration): UuidInterface
    {
        $id = Uuid::uuid4();

        try {
            $response = $this->client->sendWithBearer(
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

    public function addScopeToClient(Realm $realm, UuidInterface $clientId, UuidInterface $scopeId): void
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'PUT',
                    sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $realm->internalName, $clientId->toString(), $scopeId->toString())
                )
            );
        } catch (GuzzleException $e) {
            throw KeyCloakApiFailed::failedToAddScopeToClient($e->getMessage());
        }

        if ($response->getStatusCode() !== 204) {
            throw KeyCloakApiFailed::failedToAddScopeToClientWithResponse($response);
        }
    }

    public function fetchClient(Realm $realm, Integration $integration): Client
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'GET',
                    'admin/realms/' . $realm->internalName . '/clients?' . http_build_query(['clientId' => $integration->id->toString()])
                )
            );

            $body = $response->getBody()->getContents();

            if (empty($body) || $response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($realm, $body);
            }

            $data = Json::decodeAssociatively($body);
            return Client::createFromJson($realm, $integration->id, $data[0]);
        } catch (Exception $e) {
            throw KeyCloakApiFailed::failedToFetchClient($realm, $e->getMessage());
        }
    }
}
