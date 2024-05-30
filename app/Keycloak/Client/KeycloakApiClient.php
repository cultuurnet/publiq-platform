<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Domain\Integrations\Integration;
use App\Json;
use App\Keycloak\Client;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Converters\IntegrationToKeycloakClientConverter;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
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
    public function createClient(Realm $realm, Integration $integration, UuidInterface $id): void
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'POST',
                    sprintf('admin/realms/%s/clients', $realm->internalName),
                    [],
                    Json::encode(IntegrationToKeycloakClientConverter::convert($id, $integration, Uuid::uuid4()))
                ),
                $realm
            );
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToCreateClient($e->getMessage());
        }

        if ($response->getStatusCode() === 409) {
            /*
            When using the action "create missing clients" it could be that the client already exists in Keycloak, but not in Publiq Platform.
            In this case we do not fail, we will just connect both sides and make sure the scopes are configured correctly.
            */

            $this->logger->info(sprintf('Client %s already exists for realm %s', $integration->name, $realm->publicName));

            return;
        }

        if ($response->getStatusCode() !== 201) {
            throw KeyCloakApiFailed::failedToCreateClientWithResponse($response);
        }

        $this->logger->info(sprintf('Client %s for realm %s created with id %s', $integration->name, $realm->publicName, $id->toString()));
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
                    sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $client->realm->internalName, $client->id->toString(), $scopeId->toString())
                ),
                $client->realm
            );
        } catch (GuzzleException $e) {
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
                        sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $client->realm->internalName, $client->id->toString(), $scope->toString()),
                    ),
                    $client->realm
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
    public function fetchClient(Realm $realm, Integration $integration, UuidInterface $id): Client
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'GET',
                    sprintf('admin/realms/%s/clients/%s', $realm->internalName, $id->toString())
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
                    'admin/realms/' . $client->realm->internalName . '/clients/?' . http_build_query(['clientId' => $client->clientId->toString()])
                ),
                $client->realm
            );

            $body = $response->getBody()->getContents();

            if (empty($body) || $response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($client->realm, $body);
            }

            $data = Json::decodeAssociatively($body);

            $this->logger->info('Response: ' . $body);

            return $data[0]['enabled'];
        } catch (Throwable $e) {
            Log::error($e->getLine() . '/' . $e->getMessage());
            throw KeyCloakApiFailed::failedToFetchClient($client->realm, $e->getMessage());
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
                    'admin/realms/' . $client->realm->internalName . '/clients/' . $client->id->toString(),
                    [],
                    Json::encode($body)
                ),
                $client->realm
            );

            if ($response->getStatusCode() !== 204) {
                throw KeyCloakApiFailed::failedToUpdateClient($client);
            }
        } catch (Throwable) {
            throw KeyCloakApiFailed::failedToUpdateClient($client);
        }
    }
}
