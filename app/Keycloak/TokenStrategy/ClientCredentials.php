<?php

declare(strict_types=1);

namespace App\Keycloak\TokenStrategy;

use App\Json;
use App\Keycloak\Dto\Config;
use App\Keycloak\Dto\Realm;
use App\Keycloak\Exception\KeyCloakApiFailed;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/*
 * LIMITATION: This class currently does not refresh the token automatically.
 * For normal usage this should be ok, but if we ever implement long-running CLI processes this will need to be improved.
 * */
final class ClientCredentials implements TokenStrategy
{
    private ?string $accessToken = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly Config $config
    ) {
    }

    public function fetchToken(Realm $realm): string
    {
        if ($this->accessToken === null) { //lazy loading
            try {
                if (!$this->config->isEnabled()) {
                    throw KeyCloakApiFailed::isDisabled();
                }

                $response = $this->client->request(
                    'POST',
                    $this->config->getBaseUrl() . 'realms/' . $realm->getInternalName() . '/protocol/openid-connect/token',
                    [
                        'form_params' => [
                            'grant_type' => 'client_credentials',
                            'client_id' => $this->config->getClientId(),
                            'client_secret' => $this->config->getClientSecret(),
                        ],
                    ]
                );
            } catch (GuzzleException $e) {
                throw KeyCloakApiFailed::couldNotFetchAccessToken($e->getMessage());
            }

            if ($response->getStatusCode() !== 200) {
                throw KeyCloakApiFailed::couldNotFetchAccessToken($response->getBody()->getContents());
            }

            $json = Json::decodeAssociatively($response->getBody()->getContents());

            if (!isset($json['access_token'])) {
                throw KeyCloakApiFailed::unexpectedTokenResponse();
            }

            $this->accessToken = $json['access_token'];
        }

        return $this->accessToken;
    }
}
