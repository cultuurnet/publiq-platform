<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Keycloak\Client\KeycloakClientWithoutBearer;
use App\Keycloak\Config;
use App\Keycloak\RealmCollection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class KeycloakClientWithoutBearerTest extends TestCase
{
    private ClientInterface&MockObject $clientMock;
    private Config $config;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->config = new Config(
            true,
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            RealmCollection::getRealms(),
        );
    }

    public function test_can_retrieve_token(): void
    {
        $request = new Request('GET', '/endpoint');
        $response = new Response(200, [], 'Response body');

        $this->clientMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                return $request->getUri()->__toString() === $this->config->baseUrl . '/endpoint';
            }))
            ->willReturn($response);

        $keycloakClient = new KeycloakClientWithoutBearer($this->clientMock, $this->config);

        $result = $keycloakClient->send($request);

        $this->assertEquals('Response body', $result->getBody()->getContents());
    }
}
