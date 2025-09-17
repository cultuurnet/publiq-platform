<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Api\TokenStrategy\TokenStrategy;
use App\Keycloak\Client\KeycloakHttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class KeycloakHttpClientTest extends TestCase
{
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
            ->with($this->givenAcceptanceRealm()->getMasterRealm()->getContext())
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

    #[dataProvider('errorResponseProvider')]
    public function test_it_retries_on_unauthorized_response(Response $errorResponse): void
    {
        $keycloakClient = new KeycloakHttpClient($this->clientMock, $this->tokenStrategy);

        $this->tokenStrategy->expects($this->exactly(2))
            ->method('fetchToken')
            ->with($this->givenAcceptanceRealm()->getMasterRealm()->getContext())
            ->willReturn(self::MY_SECRET_TOKEN);

        $request = new Request('GET', '/endpoint');
        $successfulResponse = new Response(200, [], 'Successful response');

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('send')
            ->with($this->callback(function (RequestInterface $request) {
                return $request->getUri()->__toString() === $this->givenAcceptanceRealm()->getMasterRealm()->baseUrl . '/endpoint'
                    && $request->getHeader('Authorization')[0] === 'Bearer ' . self::MY_SECRET_TOKEN;
            }))
            ->willReturnOnConsecutiveCalls($errorResponse, $successfulResponse);

        $result = $keycloakClient->sendWithBearer($request, $this->givenAcceptanceRealm());

        $this->assertEquals('Successful response', $result->getBody()->getContents());
    }

    public static function errorResponseProvider(): Iterator
    {
        yield '401 Unauthorized' => [new Response(401, [], 'Unauthorized')];
        yield '403 Forbidden' => [new Response(403, [], 'Forbidden')];
    }
}
