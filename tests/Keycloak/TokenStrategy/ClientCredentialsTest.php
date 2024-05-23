<?php

declare(strict_types=1);

namespace Tests\Keycloak\TokenStrategy;

use App\Domain\Integrations\Environment;
use App\Keycloak\Config;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\Keycloak\KeycloakHelper;

final class ClientCredentialsTest extends TestCase
{
    use KeycloakHelper;
    public const ACCESS_TOKEN = 'pqeaefosdfhbsdq';

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->config = new Config(
            true,
            'https://keycloak.example.com/',
            'php_client',
            'a_true_secret',
            new RealmCollection([new Realm('uitidpoc', 'Acceptance', Environment::Acceptance)])
        );
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_it_returns_a_valid_token(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::ACCESS_TOKEN], JSON_THROW_ON_ERROR)),
        ]);

        $clientCredentials = new ClientCredentials(
            $this->config,
            $this->logger
        );
        $token = $clientCredentials->fetchToken(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            Realm::getMasterRealm()
        );

        $this->assertEquals(self::ACCESS_TOKEN, $token);
    }

    public function test_failed_because_response_did_not_contain_token(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
        ]);

        $clientCredentials = new ClientCredentials(
            $this->config,
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::UNEXPECTED_TOKEN_RESPONSE);

        $token = $clientCredentials->fetchToken(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            Realm::getMasterRealm()
        );

        $this->assertEquals(self::ACCESS_TOKEN, $token);
    }

    public function test_failed_because_unauthorized(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([], JSON_THROW_ON_ERROR)),
        ]);

        $clientCredentials = new ClientCredentials(
            $this->config,
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::COULD_NOT_FETCH_ACCESS_TOKEN);
        $clientCredentials->fetchToken(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            Realm::getMasterRealm()
        );
    }
}
