<?php

declare(strict_types=1);

namespace App\Keycloak\TokenStrategy;

use App\Json;
use App\Keycloak\Client\KeycloakClient;
use App\Keycloak\Config;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Request;

/*
 * LIMITATION: This class currently does not refresh the token automatically.
 * For normal usage this should be ok, but if we ever implement long-running CLI processes this will need to be improved.
 * */
final class ClientCredentials implements TokenStrategy
{
    private array $accessToken = [];

    public function __construct(
        private readonly KeycloakClient $client,
        private readonly Config $config,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetchToken(Realm $realm): string
    {
        $key = $realm->internalName . $this->config->clientId;

        if ($this->accessToken[$key] !== null) {
            return $this->accessToken[$key];
        }

        try {
            $request = new Request(
                'POST',
                'realms/' . $realm->internalName . '/protocol/openid-connect/token',
                [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->config->clientId,
                        'client_secret' => $this->config->clientSecret,
                    ],
                ]
            );
            $response = $this->client->send($request);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
            throw KeyCloakApiFailed::couldNotFetchAccessToken($e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger->error($response->getBody()->getContents());
            throw KeyCloakApiFailed::couldNotFetchAccessToken($response->getBody()->getContents());
        }

        $json = Json::decodeAssociatively($response->getBody()->getContents());

        if (!isset($json['access_token'])) {
            throw KeyCloakApiFailed::unexpectedTokenResponse();
        }

        $this->logger->info('Fetched token for ' . $this->config->clientId . ', token starts with ' . substr($json['access_token'], 0, 6));
        $this->accessToken[$key] = $json['access_token'];

        return $this->accessToken[$key];
    }
}