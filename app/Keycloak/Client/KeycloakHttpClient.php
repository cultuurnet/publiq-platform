<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Keycloak\Realm;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class KeycloakHttpClient
{
    public function __construct(
        private ClientInterface $client,
        private TokenStrategy $tokenStrategy,
        private LoggerInterface $logger = new NullLogger()
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

    /**
     * @throws GuzzleException
     */
    public function sendWithBearer(RequestInterface $request, Realm $realm): ResponseInterface
    {
        $request = $request
            ->withUri(new Uri($realm->baseUrl . $request->getUri()))
            ->withAddedHeader(
                'Authorization',
                'Bearer ' . $this->tokenStrategy->fetchToken($this, $realm->getMasterRealm())
            );

        $this->logger->debug('API call: ' . $request->getUri());

        return $this->client->send($request);
    }
}
