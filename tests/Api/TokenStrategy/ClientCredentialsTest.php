<?php

declare(strict_types=1);

namespace Tests\Api\TokenStrategy;

use App\Api\TokenStrategy\ClientCredentials;
use App\Keycloak\Exception\KeyCloakApiFailed;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class ClientCredentialsTest extends TestCase
{
    use KeycloakHttpClientFactory;

    use RealmFactory;

    public const ACCESS_TOKEN = 'access-token';

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_it_returns_a_valid_token(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::ACCESS_TOKEN], JSON_THROW_ON_ERROR)),
        ]);

        $clientCredentials = new ClientCredentials(
            $this->givenClient($mock),
            $this->logger
        );

        $token = $clientCredentials->fetchToken(
            $this->givenTestRealm()->getContext()
        );

        $this->assertEquals(self::ACCESS_TOKEN, $token);
    }

    public function test_failed_because_response_did_not_contain_token(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
        ]);

        $clientCredentials = new ClientCredentials(
            $this->givenClient($mock),
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::UNEXPECTED_TOKEN_RESPONSE);

        $token = $clientCredentials->fetchToken(
            $this->givenTestRealm()->getContext()
        );

        $this->assertEquals(self::ACCESS_TOKEN, $token);
    }

    public function test_failed_because_unauthorized(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([], JSON_THROW_ON_ERROR)),
        ]);

        $clientCredentials = new ClientCredentials(
            $this->givenClient($mock),
            $this->logger
        );
        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::COULD_NOT_FETCH_ACCESS_TOKEN);
        $clientCredentials->fetchToken(
            $this->givenTestRealm()->getContext()
        );
    }
}
