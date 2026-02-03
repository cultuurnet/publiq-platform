<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\ClientId\ClientIdFreeStringStrategy;
use App\Keycloak\ClientId\ClientIdUuidStrategy;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreatesTestData;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class KeycloakApiClientTest extends TestCase
{
    use KeycloakHttpClientFactory;
    use CreatesTestData;
    use RealmFactory;

    private const INTEGRATION_ID = '824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74';
    private const UUID = '824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74';
    public const SECRET = 'abra_kadabra';
    private const TOKEN = 'pqeaefosdfhbsdq';

    private Realm $realm;
    private Integration $integration;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->realm = $this->givenTestRealm();

        $this->integration = $this->givenThereIsAnIntegration(Uuid::fromString(self::INTEGRATION_ID));
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_can_create_client(): void
    {
        $clientId = Uuid::uuid4()->toString();

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(201),
            new Response(200, [], json_encode(
                [
                    'id' => self::UUID,
                    'clientId' => $clientId,
                    'name' => 'test client',
                    'secret' => self::SECRET,
                    'enabled' => true,
                ],
                JSON_THROW_ON_ERROR
            )),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger
        );

        $counter = 0;
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) use (&$counter, $clientId) {
                switch ($counter++) {
                    case 0:
                        $this->assertEquals('Fetched token for php_client, token starts with ' . substr(self::TOKEN, 0, 6), $message);
                        break;
                    case 1:
                        $this->assertEquals(sprintf('Client %s for realm %s created with client id %s', $this->integration->name, $this->realm->publicName, $clientId), $message);
                        break;
                    default:
                        $this->fail('Unknown message logged: ' . $message);
                }
            });

        $apiClient->createClient(
            $this->realm,
            $this->integration,
            new ClientIdFreeStringStrategy($clientId)
        );
    }

    public function test_fails_to_create_client(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(500),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_CREATE_CLIENT_WITH_RESPONSE);

        $apiClient->createClient(
            $this->realm,
            $this->integration,
            new ClientIdUuidStrategy(),
        );
    }

    public function test_fails_to_add_scope_to_client(): void
    {
        $scopeId = Uuid::fromString('123ae05d-1c41-40c8-8716-c4654a3bfd98');

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(500),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_ADD_SCOPE_WITH_RESPONSE);

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4()->toString(), self::SECRET, Environment::Acceptance);

        $apiClient->addScopeToClient(
            $client,
            $scopeId
        );
    }

    #[DataProvider('dataProviderIsClientEnabled')]
    public function test_fetch_is_client_enabled(bool $enabled): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['enabled' => $enabled], JSON_THROW_ON_ERROR)),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger
        );

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4()->toString(), self::SECRET, Environment::Acceptance);

        $this->assertEquals($enabled, $apiClient->fetchIsClientActive($client));
    }

    public static function dataProviderIsClientEnabled(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function test_update_client_throws_exception_when_api_call_fails(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(500),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger
        );

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4()->toString(), self::SECRET, Environment::Acceptance);

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_UPDATE_CLIENT);

        $apiClient->updateClient($client, []);
    }

    public function test_reset_scopes_throws_exception_when_api_call_fails(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(500),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger
        );

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4()->toString(), self::SECRET, Environment::Acceptance);

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_RESET_SCOPE);

        $apiClient->deleteScopes($client);
    }
}
