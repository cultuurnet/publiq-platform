<?php

declare(strict_types=1);

namespace Tests\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Keycloak\Config;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\Service\ApiClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\IntegrationHelper;
use Tests\Keycloak\KeycloakHelper;

final class ApiClientTest extends TestCase
{
    use KeycloakHelper;
    use IntegrationHelper;

    private const INTEGRATION_ID = '824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74';
    public const SECRET = 'abra_kadabra';

    private Realm $realm;
    private Integration $integration;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->config = new Config(
            true,
            'https://keycloak.example.com/',
            'php_client',
            'a_true_secret',
            new RealmCollection([new Realm('uitidpoc', 'Acceptance')])
        );

        $this->realm = new Realm('uitidpoc', 'Acceptance');
        $this->integration = $this->createIntegration(Uuid::fromString(self::INTEGRATION_ID));
        $this->logger = $this->createMock(LoggerInterface::class);
        ;
    }

    public function test_can_create_client(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'pqeaefosdfhbsdq'], JSON_THROW_ON_ERROR)),
            new Response(201),
        ]);

        $apiClient = new ApiClient(
            $this->createKeycloakClientWithBearer($this->logger, $mock),
            $this->logger
        );

        $id = $apiClient->createClient(
            $this->realm,
            $this->integration
        );

        $this->assertInstanceOf(UuidInterface::class, $id);
    }

    public function test_fails_to_create_client(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'pqeaefosdfhbsdq'], JSON_THROW_ON_ERROR)),
            new Response(500),
        ]);

        $apiClient = new ApiClient(
            $this->createKeycloakClientWithBearer($this->logger, $mock),
            $this->logger
        );

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::FAILED_TO_CREATE_CLIENT_WITH_RESPONSE);

        $apiClient->createClient(
            $this->realm,
            $this->integration
        );
    }
}
