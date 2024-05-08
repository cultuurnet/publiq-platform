<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Keycloak\Config;
use App\Keycloak\Realm;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class KeycloakClientWithBearer implements KeycloakClient
{
    public function __construct(private ClientInterface $client, private Config $config, private TokenStrategy $tokenStrategy)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $request = $request
            ->withUri(new Uri($this->config->baseUrl . $request->getUri()))
            ->withAddedHeader('Authorization', 'Bearer ' . $this->tokenStrategy->fetchToken(Realm::getMasterRealm()));
        return $this->client->send($request);
    }
}
