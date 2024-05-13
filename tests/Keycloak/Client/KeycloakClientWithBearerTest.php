<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Keycloak\Client\KeycloakClientWithBearer;
use App\Keycloak\Config;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class KeycloakClientWithBearerTest extends TestCase
{
    public const MY_SECRET_TOKEN = 'my-secret-token';
    private ClientInterface&MockObject $clientMock;
    private Config $config;
    private TokenStrategy&MockObject $tokenStrategy;

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
        $this->tokenStrategy = $this->createMock(TokenStrategy::class);
        $this->tokenStrategy->expects($this->once())
            ->method('fetchToken')
            ->with(Realm::getMasterRealm())
            ->willReturn(self::MY_SECRET_TOKEN);
    }

    public function can_send_a_request_with_bearer(): void
    {
        $request = new Request('GET', '/endpoint');
        $response = new Response(200, [], 'Response body');

        $this->clientMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                return $request->getUri()->__toString() === $this->config->baseUrl . '/endpoint'
                    && $request->getHeader('Authorization')[0] === 'Bearer ' . self::MY_SECRET_TOKEN;
            }))
            ->willReturn($response);

        $keycloakClient = new KeycloakClientWithBearer($this->clientMock, $this->config, $this->tokenStrategy);

        $result = $keycloakClient->send($request);

        $this->assertEquals('Response body', $result->getBody()->getContents());
    }
}
