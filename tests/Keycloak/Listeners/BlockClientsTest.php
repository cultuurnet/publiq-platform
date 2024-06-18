<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Listeners\BlockClients;
use App\Keycloak\Repositories\KeycloakClientRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class BlockClientsTest extends TestCase
{
    use CreatesIntegration;
    use KeycloakHttpClientFactory;
    use RealmFactory;

    private const SECRET = 'my-secret';
    private const INTEGRATION_ID = '3f2c8aa3-6d5d-4a72-ba41-ab26bc8e591d';

    private Integration $integration;
    private ApiClient&MockObject $apiClient;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();

        // This is a search API integration
        $this->integration = $this->givenThereIsAnIntegration(Uuid::fromString(self::INTEGRATION_ID));

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     *@dataProvider differentWaysToBlockClients
     */
    public function test_block_clients_when_integration_is_blocked_or_deleted(IntegrationBlocked|IntegrationDeleted $event): void
    {
        $integrationRepository = $this->createMock(IntegrationRepository::class);
        $integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $clients = [];
        foreach ($this->givenAllRealms()
                 as $realm) {
            $client = new Client(Uuid::uuid4(), $this->integration->id, Uuid::uuid4()->toString(), self::SECRET, $realm->environment);

            $clients[$client->id->toString()] = $client;
        }

        $this->apiClient->expects($this->exactly($this->givenAllRealms()->count()))
            ->method('blockClient')
            ->willReturnCallback(function (Client $client) use ($clients) {
                $this->assertArrayHasKey($client->id->toString(), $clients);
            });

        $this->logger->expects($this->exactly($this->givenAllRealms()->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) {
                $this->assertEquals('Keycloak client blocked', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('environment', $options);

                $this->assertEquals($this->integration->id->toString(), $options['integration_id']);
            });

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($this->integration->id)
            ->willReturn($clients);

        $createClients = new BlockClients(
            $integrationRepository,
            $keycloakClientRepository,
            $this->apiClient,
            $this->logger
        );

        $createClients->handle($event);
    }

    public static function differentWaysToBlockClients() : array
    {
        return [
            [new IntegrationBlocked(Uuid::fromString(self::INTEGRATION_ID))],
            [new IntegrationDeleted(Uuid::fromString(self::INTEGRATION_ID))],
        ];
    }
}
