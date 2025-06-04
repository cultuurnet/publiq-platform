<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Domain\Integrations\Environment;
use App\Keycloak\Realm;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/*
 * Difference between this class and KeycloakHttpClient is that this fetches a token from the uitid realm, not the master realm
 * It also calculates the base url differently
 * */
final readonly class UitpasHttpClient implements HttpClient
{
    public function __construct(
        private ClientInterface $client,
        private TokenStrategy $tokenStrategy,
        private string $testApiEndpoint,
        private string $prodApiEndpoint,
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function sendWithoutBearer(RequestInterface $request, Realm $realm): ResponseInterface
    {
        $request = $request
            ->withUri(new Uri($realm->baseUrl . $request->getUri()));

        return $this->client->send($request);
    }

    /** @throws GuzzleException */
    public function sendWithBearer(RequestInterface $request, Realm $realm): ResponseInterface
    {
        $request = $request
            ->withUri(new Uri($this->getEndpoint($realm) . $request->getUri()))
            ->withAddedHeader(
                'Authorization',
                'Bearer ' . $this->tokenStrategy->fetchToken($this, $realm)
            );

        return $this->client->send($request);
    }

    private function getEndpoint(Realm $keycloakClient): string
    {
        if ($keycloakClient->environment === Environment::Testing) {
            return $this->testApiEndpoint;
        }

        return $this->prodApiEndpoint;
    }
}
