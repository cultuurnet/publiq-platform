<?php

declare(strict_types=1);

namespace Tests\Keycloak\Client;

use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use App\Keycloak\ScopeConfig;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\Keycloak\ConfigFactory;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;

final class KeycloakApiClientTest extends TestCase
{
    use KeycloakHttpClientFactory;
    use CreatesIntegration;
    use RealmFactory;
    use ConfigFactory;

    private const INTEGRATION_ID = '824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74';
    private const UUID = '824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74';
    public const SECRET = 'abra_kadabra';
    private const TOKEN = 'pqeaefosdfhbsdq';

    private Realm $realm;
    private Integration $integration;
    private LoggerInterface&MockObject $logger;
    private ScopeConfig $scopeConfig;

    protected function setUp(): void
    {
        $this->realm = $this->givenTestRealm();
        $this->config = $this->givenKeycloakConfig($this->realm);

        $this->integration = $this->givenThereIsAnIntegration(Uuid::fromString(self::INTEGRATION_ID));
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->scopeConfig = new ScopeConfig(
            Uuid::fromString('824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74'),
            Uuid::fromString('d8a54568-26da-412b-a441-d5e2fad84478'),
            Uuid::fromString('123ae05d-1c41-40c8-8716-c4654a3bfd98'),
            Uuid::fromString('0743b1c7-0ea2-46af-906e-fbb6c0317514'),
        );
    }

    public function test_can_create_client(): void
    {
        $clientId = Uuid::uuid4();
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(201),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->scopeConfig,
            $this->logger
        );

        $counter = 0;
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) use (&$counter) {
                switch ($counter++) {
                    case 0:
                        $this->assertEquals('Fetched token for php_client, token starts with ' . substr(self::TOKEN, 0, 6), $message);
                        break;
                    case 1:

                        // use contains because we don't know the generated id
                        $this->assertStringStartsWith(
                            sprintf('Client %s, client id %s created with id', $this->integration->name, $this->integration->id),
                            $message
                        );

                        break;
                    default:
                        $this->fail('Unknown message logged: ' . $message);
                }
            });

        $apiClient->createClient(
            $this->realm,
            $this->integration,
            $clientId,
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
            $this->scopeConfig,
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_CREATE_CLIENT_WITH_RESPONSE);

        $apiClient->createClient(
            $this->realm,
            $this->integration,
            Uuid::uuid4(),
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
            $this->scopeConfig,
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_ADD_SCOPE_WITH_RESPONSE);

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4(), self::SECRET, $this->realm);

        $apiClient->addScopeToClient(
            $client,
            $scopeId
        );
    }

    public function test_can_fetch_client(): void
    {
        $clientId = Uuid::uuid4();

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(
                [
                    [
                        'id' => self::UUID,
                        'clientId' => $clientId,
                        'name' => 'test client',
                        'secret' => self::SECRET,
                        'enabled' => true,
                    ],
                ],
                JSON_THROW_ON_ERROR
            )),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->scopeConfig,
            $this->logger
        );

        $client = $apiClient->fetchClient(
            $this->realm,
            $this->givenThereIsAnIntegration(Uuid::fromString(self::INTEGRATION_ID)),
            $clientId
        );

        $this->assertEquals(self::UUID, $client->id->toString());
        $this->assertEquals(self::INTEGRATION_ID, $client->integrationId->toString());
        $this->assertEquals($clientId, $client->clientId->toString());
        $this->assertEquals(self::SECRET, $client->clientSecret);
        $this->assertEquals($this->realm, $client->realm);
    }

    public function test_client_not_found(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(500, [], 'It is broken'),
        ]);

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_FETCH_CLIENT);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->scopeConfig,
            $this->logger
        );

        $client = $apiClient->fetchClient($this->realm, $this->integration, Uuid::uuid4());
        $this->assertEmpty($client);
    }

    /** @dataProvider dataProviderIsClientEnabled */
    public function test_fetch_is_client_enabled(bool $enabled): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([['enabled' => $enabled]], JSON_THROW_ON_ERROR)),
        ]);

        $apiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->scopeConfig,
            $this->logger
        );

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4(), self::SECRET, $this->givenAcceptanceRealm());

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
            $this->scopeConfig,
            $this->logger
        );

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4(), self::SECRET, $this->realm);

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
            $this->scopeConfig,
            $this->logger
        );

        $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4(), self::SECRET, $this->realm);

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_RESET_SCOPE);

        $apiClient->deleteScopes($client);
    }
}
