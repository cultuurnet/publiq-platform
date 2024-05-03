<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Json;
use App\Keycloak\Collection\ClientCollection;
use App\Keycloak\Dto\Client;
use App\Keycloak\Dto\Config;
use App\Keycloak\Dto\Realm;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Log\LoggerInterface;

final readonly class ApiClient
{
    public function __construct(
        private ClientInterface $client,
        private Config $config,
        private TokenStrategy $tokenStrategy,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return ClientCollection[]
     */
    public function fetchClients(string $clientId): array
    {
        $output = [];
        foreach ($this->config->getRealms() as $realm) {
            $output[$realm->getInternalName()] = $this->fetchClientsPerRealm($realm, $clientId);
        }

        return $output;
    }

    private function fetchClientsPerRealm(Realm $realm, string $clientId): ClientCollection
    {
        $clientCollection = new ClientCollection();

        try {
            $response = $this->client->request(
                'GET',
                $this->config->getBaseUrl() . 'admin/realms/' . $realm->getInternalName() . '/clients',
                [
                    // @todo Not sure if I need to filter on clientId, or some meta information field - more a proof of concept
                    'query' => ['clientId' => $clientId],
                    'headers' => $this->getHeaders(),
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::failedToFetchClient($realm, $response->getBody()->getContents());
            }

            $body = $response->getBody()->getContents();
            foreach (Json::decodeAssociatively($body) as $client) {
                $clientCollection->add(Client::fromJson($client));
            }
        } catch (KeyCloakApiFailed|JsonException|GuzzleException $e) {
            $this->logger->error($e->getMessage());
        }

        return $clientCollection;
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->tokenStrategy->fetchToken(Realm::getMasterRealm()),
        ];
    }
}
