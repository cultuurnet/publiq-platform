<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Listeners\UpdateClients;
use App\Keycloak\Realms;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Converters\IntegrationToKeycloakClientConverter;
use App\Keycloak\Converters\IntegrationUrlConverter;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\CreatesIntegration;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class UpdateClientsTest extends TestCase
{
    use CreatesIntegration;
    use KeycloakHttpClientFactory;

    use RealmFactory;

    private const SECRET = 'my-secret';
    private const SEARCH_SCOPE_ID = '06059529-74b5-422a-a499-ffcaf065d437';

    private Integration $integration;
    private ScopeConfig $scopeConfig;
    private ApiClient&MockObject $apiClient;
    private LoggerInterface&MockObject $logger;
    private IntegrationRepository&MockObject $integrationRepository;
    private Realms $realms;

    protected function setUp(): void
    {
        parent::setUp();

        // This is a search API integration
        $this->integration = $this->givenThereIsAnIntegration(Uuid::uuid4());

        $this->integration = $this->integration->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $this->integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://example.com/callback'),
            new IntegrationUrl(Uuid::uuid4(), $this->integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login1'),
            new IntegrationUrl(Uuid::uuid4(), $this->integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login2'),
            new IntegrationUrl(Uuid::uuid4(), $this->integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout1'),
            new IntegrationUrl(Uuid::uuid4(), $this->integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout2'),
        );

        $this->scopeConfig = new ScopeConfig(
            Uuid::fromString(self::SEARCH_SCOPE_ID),
            Uuid::fromString('d8a54568-26da-412b-a441-d5e2fad84478'),
            Uuid::fromString('123ae05d-1c41-40c8-8716-c4654a3bfd98'),
            Uuid::fromString('0743b1c7-0ea2-46af-906e-fbb6c0317514'),
        );

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);
        $this->realms = $this->givenAllRealms();
    }

    public function test_update_client_for_integration(): void
    {
        $clients = [];
        foreach ($this->realms as $realm) {
            $id = Uuid::uuid4();
            $clients[$id->toString()] = new Client($id, $this->integration->id, Uuid::uuid4(), self::SECRET, $realm->environment);
        }

        $activeId = null; // Which client are we updating?
        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('updateClient')
            ->willReturnCallback(function (Client $client, array $body) use (&$activeId) {
                $expectedBody = array_merge(
                    IntegrationToKeycloakClientConverter::convert($client->id, $this->integration, $client->clientId),
                    IntegrationUrlConverter::convert($this->integration, $client)
                );

                $this->assertEquals($expectedBody, $body);

                $activeId = $client->id;
            });

        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('deleteScopes')
            ->willReturnCallback(function (Client $client) use (&$activeId) {
                $this->assertEquals($activeId, $client->id);
            });

        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('addScopeToClient')
            ->willReturnCallback(function (Client $client, UuidInterface $scopeId) use (&$activeId) {
                $this->assertEquals($activeId, $client->id);
                $this->assertEquals(Uuid::fromString(self::SEARCH_SCOPE_ID), $scopeId);
            });

        $this->logger->expects($this->exactly($this->realms->count()))
            ->method('info')
            ->willReturnCallback(function (string $message, array $params) use (&$activeId, $clients) {
                $this->assertEquals('Keycloak client updated', $message);

                if ($activeId === null || !isset($clients[$activeId->toString()])) {
                    $this->fail('Logging client that does not exist');
                }

                $client = $clients[$activeId->toString()];

                $this->assertEquals([
                    'integration_id' => $this->integration->id->toString(),
                    'environment' => $client->environment->value,
                    'client_id' => $client->clientId->toString(),
                ], $params);
            });

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($this->integration->id)
            ->willReturn($clients);

        $updateClients = new UpdateClients(
            $this->integrationRepository,
            $keycloakClientRepository,
            $this->apiClient,
            $this->scopeConfig,
            $this->logger
        );

        $updateClients->handle(new IntegrationUpdated($this->integration->id));
    }
}
