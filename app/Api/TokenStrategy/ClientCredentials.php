<?php

declare(strict_types=1);

namespace App\Api\TokenStrategy;

use App\Api\ClientCredentialsContext;
use App\Json;
use App\Keycloak\Exception\KeyCloakApiFailed;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Log\LoggerInterface;

/*
 * LIMITATION: This class currently does not refresh the token automatically.
 * For normal usage this should be ok, but if we ever implement long-running CLI processes this will need to be improved.
 * */

final class ClientCredentials implements TokenStrategy
{
    /** @var array<string, string> */
    private array $accessToken = [];

    public function __construct(
        private readonly ClientInterface $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetchToken(ClientCredentialsContext $context): string
    {
        $key = $context->getCacheKey();

        if (isset($this->accessToken[$key])) {
            return $this->accessToken[$key];
        }

        try {
            $request = new Request(
                'POST',
                'realms/' . $context->realmName . '/protocol/openid-connect/token',
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                http_build_query([
                    'grant_type' => 'client_credentials',
                    'client_id' => $context->clientId,
                    'client_secret' => $context->clientSecret,
                ])
            );

            $response = $this->client->send(
                $request->withUri(new Uri($context->baseUrl . $request->getUri()))
            );
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
            throw KeyCloakApiFailed::couldNotFetchAccessToken($e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            $message = $response->getBody()->getContents();
            $this->logger->error($message);
            throw KeyCloakApiFailed::couldNotFetchAccessToken($message);
        }

        $json = Json::decodeAssociatively($response->getBody()->getContents());

        if (!isset($json['access_token'])) {
            throw KeyCloakApiFailed::unexpectedTokenResponse();
        }

        $token = $json['access_token'];
        $this->logger->info('Fetched token for ' . $context->clientId . ', token starts with ' . substr($token, 0, 6));
        $this->accessToken[$key] = $token;

        return $token;
    }

    public function clearToken(ClientCredentialsContext $context): void
    {
        unset($this->accessToken[$context->getCacheKey()]);
    }
}
