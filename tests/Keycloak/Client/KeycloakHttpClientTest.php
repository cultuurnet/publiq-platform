<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Keycloak\Client\KeycloakHttpClient;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Keycloak\ConfigFactory;
use Tests\Keycloak\RealmFactory;

final class KeycloakHttpClientTest extends TestCase
{
    use ConfigFactory;
    use RealmFactory;

    public const MY_SECRET_TOKEN = 'my-secret-token';
    private ClientInterface&MockObject $clientMock;
    private TokenStrategy&MockObject $tokenStrategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->tokenStrategy = $this->createMock(TokenStrategy::class);
    }

    public function test_it_can_send_a_request_with_bearer(): void
    {
        $keycloakClient = new KeycloakHttpClient($this->clientMock, $this->tokenStrategy);

        $this->tokenStrategy->expects($this->once())
            ->method('fetchToken')
            ->with($keycloakClient, $this->givenAcceptanceRealm()->getMasterRealm())
            ->willReturn(self::MY_SECRET_TOKEN);

        $request = new Request('GET', '/endpoint');
        $response = new Response(200, [], 'Response body');

        $this->clientMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                return $request->getUri()->__toString() === $this->givenAcceptanceRealm()->getMasterRealm()->baseUrl . '/endpoint'
                    && $request->getHeader('Authorization')[0] === 'Bearer ' . self::MY_SECRET_TOKEN;
            }))
            ->willReturn($response);

        $result = $keycloakClient->sendWithBearer($request, $this->givenAcceptanceRealm());

        $this->assertEquals('Response body', $result->getBody()->getContents());
    }

    public function test_it_can_send_a_request_without_bearer(): void
    {
        $request = new Request('GET', '/endpoint');
        $response = new Response(200, [], 'Response body');

        $this->clientMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                return $request->getUri()->__toString() === $this->givenAcceptanceRealm()->baseUrl . '/endpoint';
            }))
            ->willReturn($response);

        $keycloakClient = new KeycloakHttpClient($this->clientMock, $this->tokenStrategy);

        $result = $keycloakClient->sendWithoutBearer($request, $this->givenAcceptanceRealm());

        $this->assertEquals('Response body', $result->getBody()->getContents());
    }
}
