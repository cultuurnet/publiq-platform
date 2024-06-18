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
use App\Keycloak\Realms;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Session;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final readonly class KeycloakApiClient implements ApiClient
{
    public function __construct(
        private KeycloakHttpClient $client,
        private Realms $realms,
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
        $id = Uuid::uuid4();

        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'POST',
                    sprintf('admin/realms/%s/clients', $realm->internalName),
                    [],
                    Json::encode(IntegrationToKeycloakClientConverter::convert(
                        $id,
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

        return $this->fetchClient($realm, $integration, $id->toString());
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function addScopeToClient(Client $client, UuidInterface $scopeId): void
    {
        $realm = $this->realms->getRealmByEnvironment($client->environment);

        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'PUT',
                    sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $realm->internalName, $client->id->toString(), $scopeId->toString())
                ),
                $realm
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
        $realm = $this->realms->getRealmByEnvironment($client->environment);

        foreach ($realm->scopeConfig->getAll() as $scope) {
            try {
                $response = $this->client->sendWithBearer(
                    new Request(
                        'DELETE',
                        sprintf('admin/realms/%s/clients/%s/default-client-scopes/%s', $realm->internalName, $client->id->toString(), $scope->toString()),
                    ),
                    $realm
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
    private function fetchClient(Realm $realm, Integration $integration, string $id): Client
    {
        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'GET',
                    sprintf('admin/realms/%s/clients/%s', $realm->internalName, $id)
                ),
                $realm
            );

            $body = $response->getBody()->getContents();

            if (empty($body) || $response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($realm, $body);
            }

            return Client::createFromJson($realm, $integration->id, Json::decodeAssociatively($body));
        } catch (KeyCloakApiFailed $e) {
            throw KeyCloakApiFailed::failedToFetchClient($realm, $e->getMessage());
        }
    }

    /**
     * @throws KeyCloakApiFailed
     */
    public function fetchIsClientActive(Client $client): bool
    {
        $realm = $this->realms->getRealmByEnvironment($client->environment);

        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'GET',
                    sprintf('admin/realms/%s/clients/%s', $realm->internalName, $client->id->toString())
                ),
                $realm
            );

            $body = $response->getBody()->getContents();

            if (empty($body) || $response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($realm, $body);
            }

            $data = Json::decodeAssociatively($body);

            return $data['enabled'];
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToFetchClient($realm, $e->getMessage());
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
        $realm = $this->realms->getRealmByEnvironment($client->environment);

        try {
            $response = $this->client->sendWithBearer(
                new Request(
                    'PUT',
                    'admin/realms/' . $realm->internalName . '/clients/' . $client->id->toString(),
                    [],
                    Json::encode($body)
                ),
                $realm
            );

            if ($response->getStatusCode() !== 204) {
                throw KeyCloakApiFailed::failedToUpdateClient($client);
            }
        } catch (Throwable) {
            throw KeyCloakApiFailed::failedToUpdateClient($client);
        }
    }

    public function exchangeToken(Realm $realm, string $authorizationCode): bool
    {
        try {
            $request = new Request(
                'POST',
                'realms/' . $realm->internalName . '/protocol/openid-connect/token',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                http_build_query([
                    'grant_type' => 'client_credentials',
                    'client_id' => $realm->clientId,
                    'client_secret' => $realm->clientSecret,
                    'code' => $authorizationCode,
                ])
            );

            $response = $this->client->sendWithoutBearer(
                $request,
                $realm
            );

            if ($response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToExchangeToken($response->getBody()->getContents());
            }
        } catch (Throwable $e) {
            throw KeyCloakApiFailed::failedToExchangeToken($e->getMessage());
        }

        $body = Json::decodeAssociatively($response->getBody()->getContents());

        //@ todo check scope ? - difference for admin / frontend

        Session::put('token', $body['access_token']);

        return true;
    }
}
