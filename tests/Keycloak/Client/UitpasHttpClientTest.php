<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Keycloak\Client\UitpasHttpClient;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Keycloak\RealmFactory;

final class UitpasHttpClientTest extends TestCase
{
    use RealmFactory;

    public const MY_SECRET_TOKEN = 'my-secret-token';
    private ClientInterface&MockObject $clientMock;
    private TokenStrategy&MockObject $tokenStrategy;
    private UitpasHttpClient $uitpasHttpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->tokenStrategy = $this->createMock(TokenStrategy::class);
        $this->uitpasHttpClient = new UitpasHttpClient($this->clientMock, $this->tokenStrategy, 'http://test-uitpas.publiq.be', 'http://test-uitpas.publiq.be');
    }

    public function test_it_can_send_a_request_with_bearer(): void
    {
        $this->tokenStrategy->expects($this->once())
            ->method('fetchToken')
            ->with($this->uitpasHttpClient, $this->givenAcceptanceRealm())
            ->willReturn(self::MY_SECRET_TOKEN);

        $request = new Request('GET', '/endpoint');
        $response = new Response(200, [], 'Response body');

        $this->clientMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                return $request->getUri()->__toString() === 'http://test-uitpas.publiq.be/endpoint'
                    && $request->getHeader('Authorization')[0] === 'Bearer ' . self::MY_SECRET_TOKEN;
            }))
            ->willReturn($response);

        $result = $this->uitpasHttpClient->sendWithBearer($request, $this->givenAcceptanceRealm());

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

        $result = $this->uitpasHttpClient->sendWithoutBearer($request, $this->givenAcceptanceRealm());

        $this->assertEquals('Response body', $result->getBody()->getContents());
    }
}
