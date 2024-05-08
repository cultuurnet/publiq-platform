<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Keycloak\Config;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class KeycloakClientWithoutBearer implements KeycloakClient
{
    public function __construct(private ClientInterface $client, private Config $config)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $request = $request
            ->withUri(new Uri($this->config->baseUrl . $request->getUri()));

        return $this->client->send($request);
    }
}
