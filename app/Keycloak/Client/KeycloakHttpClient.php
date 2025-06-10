<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Api\TokenStrategy\TokenStrategy;
use App\Keycloak\Realm;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class KeycloakHttpClient
{
    public function __construct(
        private ClientInterface $client,
        private TokenStrategy $tokenStrategy,
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function sendWithBearer(RequestInterface $request, Realm $realm): ResponseInterface
    {
        $request = $request
            ->withUri(new Uri($realm->baseUrl . $request->getUri()))
            ->withAddedHeader(
                'Authorization',
                'Bearer ' . $this->tokenStrategy->fetchToken($realm->getMasterRealm()->getContext())
            );

        return $this->client->send($request);
    }
}
