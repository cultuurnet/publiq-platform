<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Domain\Integrations\Integration;
use App\Json;
use App\Keycloak\Client;
use App\Keycloak\ClientId\ClientIdFactory;
use App\Keycloak\Converters\IntegrationToKeycloakClientConverter;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use App\Keycloak\ScopeConfig;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final readonly class KeycloakApiClient implements ApiClient
{
    public function __construct(
        private KeycloakHttpClient $client,
        private ScopeConfig $scopeConfig,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function createClient(
        Realm $realm,
        Integration $integration,
        ClientIdFactory $clientIdFactory
    ): Client {
        $clientId = $clientIdFactory->create();

        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'POST',
                    sprintf('admin/realms/%s/clients', $realm->internalName),
                    [],
                    Json::encode(IntegrationToKeycloakClientConverter::convert(
                        Uuid::uuid4(),
                        $integration,
                        $clientId
                    ))
                ),
                $realm
            );
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToCreateClient($e->getMessage());
        }

        if ($response->getStatusCode() !== 201) {
            throw KeyCloakApiFailed::failedToCreateClientWithResponse($response);
        }

        $this->logger->info(sprintf('Client %s for realm %s created with client id %s', $integration->name, $realm->publicName, $clientId));

        return $this->fetchClient($realm, $integration, $clientId);
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function addScopeToClient(Client $client, UuidInterface $scopeId): void
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'PUT',
                    sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $client->getRealm()->internalName, $client->id->toString(), $scopeId->toString())
                ),
                $client->getRealm()
            );
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToAddScopeToClient($e->getMessage());
        }

        if ($response->getStatusCode() !== 204) {
            throw KeyCloakApiFailed::failedToAddScopeToClientWithResponse($response);
        }
    }

    public function deleteScopes(Client $client): void
    {
        foreach ($this->scopeConfig->getAll() as $scope) {
            try {
                $response = $this->client->sendWithBearer(
                    new Request(
                        'DELETE',
                        sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $client->getRealm()->internalName, $client->id->toString(), $scope->toString()),
                    ),
                    $client->getRealm()
                );

                // Will throw a 404 when scope not attached to client, but this is no problem.
                if ($response->getStatusCode() !== 204 && $response->getStatusCode() !== 404) {
                    throw KeyCloakApiFailed::failedToResetScopeWithResponse($client, $scope, $response->getBody()->getContents());
                }
            } catch (Throwable) {
                throw KeyCloakApiFailed::failedToResetScope($client, $scope);
            }
        }
    }

    /**
     * @throws KeyCloakApiFailed
     */
    private function fetchClient(Realm $realm, Integration $integration, string $clientId): Client
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'GET',
                    sprintf('admin/realms/%s/clients/%s', $realm->internalName, $clientId)
                ),
                $realm
            );

            $body = $response->getBody()->getContents();

            if (empty($body) || $response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($realm, $body);
            }

            return Client::createFromJson($realm, $integration->id, Json::decodeAssociatively($body));
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToFetchClient($realm, $e->getMessage());
        }
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function fetchIsClientActive(Client $client): bool
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'GET',
                    sprintf('admin/realms/%s/clients/%s', $client->getRealm()->internalName, $client->id->toString())
                ),
                $client->getRealm()
            );

            $body = $response->getBody()->getContents();

            if (empty($body) || $response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($client->getRealm(), $body);
            }

            $data = Json::decodeAssociatively($body);

            return $data['enabled'];
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToFetchClient($client->getRealm(), $e->getMessage());
        }
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function unblockClient(Client $client): void
    {
        $this->updateClient($client, ['enabled' => true]);
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function blockClient(Client $client): void
    {
        $this->updateClient($client, ['enabled' => false]);
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function updateClient(Client $client, array $body): void
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'PUT',
                    'admin/realms/' . $client->getRealm()->internalName . '/clients/' . $client->id->toString(),
                    [],
                    Json::encode($body)
                ),
                $client->getRealm()
            );

            if ($response->getStatusCode() !== 204) {
                throw KeyCloakApiFailed::failedToUpdateClient($client);
            }
        } catch (Throwable) {
            throw KeyCloakApiFailed::failedToUpdateClient($client);
        }
    }
}
