<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Environments;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Events\MissingClientsDetected;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Realm;
use App\Keycloak\Realms;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\CreatesIntegration;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class CreateClientsTest extends TestCase
{
    use CreatesIntegration;

    use RealmFactory;

    private const SECRET = 'my-secret';
    private const SEARCH_SCOPE_ID = '06059529-74b5-422a-a499-ffcaf065d437';

    private Integration $integration;
    private CreateClients $handler;
    private IntegrationRepository&MockObject $integrationRepository;
    private KeycloakClientRepository&MockObject $keycloakClientRepository;
    private ApiClient&MockObject $apiClient;
    private LoggerInterface&MockObject $logger;
    private Realms $realms;

    protected function setUp(): void
    {
        parent::setUp();

        // This is a search API integration
        $this->integration = $this->givenThereIsAnIntegration(Uuid::uuid4());

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $this->apiClient = $this->createMock(ApiClient::class);

        $scopeConfig = new ScopeConfig(
            Uuid::fromString(self::SEARCH_SCOPE_ID),
            Uuid::fromString('d8a54568-26da-412b-a441-d5e2fad84478'),
            Uuid::fromString('123ae05d-1c41-40c8-8716-c4654a3bfd98'),
            Uuid::fromString('0743b1c7-0ea2-46af-906e-fbb6c0317514'),
        );

        $this->realms = $this->givenAllRealms();

        $this->handler = new CreateClients(
            $this->integrationRepository,
            $this->keycloakClientRepository,
            $this->realms,
            $this->apiClient,
            $scopeConfig,
            $this->logger,
        );
    }

    public function test_create_client_for_integration(): void
    {
        $clients = [];

        foreach ($this->realms as $realm) {
            $clients[$realm->internalName] = new Client(
                Uuid::uuid4(),
                $this->integration->id,
                Uuid::uuid4(),
                self::SECRET,
                $realm->environment
            );
        }

        $activeId = null;
        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('createClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clients, &$activeId) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->internalName, $clients);

                    $activeId = $clients[$realm->internalName]->id;
                    return $clients[$realm->internalName]->id;
                }
            );

        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('addScopeToClient')
            ->willReturnCallback(function (Client $client, UuidInterface $scopeId) use (&$activeId) {
                $this->assertEquals($activeId, $client->id);
                $this->assertEquals(Uuid::fromString(self::SEARCH_SCOPE_ID), $scopeId);
            });

        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('fetchClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clients) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->internalName, $clients);

                    return $clients[$realm->internalName];
                }
            );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $this->keycloakClientRepository->expects($this->once())
            ->method('create')
            ->with(... $clients);

        $this->logger->expects($this->exactly($this->realms->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('realm', $options);

                $this->assertEquals($this->integration->id->toString(), $options['integration_id']);
            });

        $this->handler->handleCreateClients(new IntegrationCreated($this->integration->id));
    }

    public function test_failed(): void
    {
        $integrationId = Uuid::uuid4();
        $throwable = new \Exception('Test exception');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to create Keycloak clients', [
                'integration_id' => $integrationId,
                'exception' => $throwable,
            ]);

        $this->handler->failed(new IntegrationCreated($integrationId), $throwable);
    }

    public function test_handle_creating_missing_clients(): void
    {
        $clients = [];

        $missingEnvironments = new Environments([Environment::Testing]);

        $this->keycloakClientRepository->expects($this->once())
            ->method('getMissingEnvironmentsByIntegrationId')
            ->with($this->integration->id)
            ->willReturn($missingEnvironments);

        foreach ($missingEnvironments as $environment) {
            $clients[$environment->value] = new Client(
                Uuid::uuid4(),
                $this->integration->id,
                Uuid::uuid4(),
                self::SECRET,
                $environment
            );
        }

        $this->apiClient->expects($this->exactly($missingEnvironments->count()))
            ->method('createClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clients) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);

                    $env = $realm->environment->value;
                    $this->assertArrayHasKey($env, $clients);
                }
            );

        $this->apiClient->expects($this->exactly($missingEnvironments->count()))
            ->method('addScopeToClient')
            ->willReturnCallback(function (Client $client, UuidInterface $scopeId) {
                $this->assertEquals(Uuid::fromString(self::SEARCH_SCOPE_ID), $scopeId);
            });

        $this->apiClient->expects($this->exactly($missingEnvironments->count()))
            ->method('fetchClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clients) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->environment->value, $clients);

                    return $clients[$realm->environment->value];
                }
            );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $this->keycloakClientRepository->expects($this->once())
            ->method('create')
            ->with(... $clients);

        $this->logger->expects($this->exactly($missingEnvironments->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('realm', $options);

                $this->assertEquals($this->integration->id->toString(), $options['integration_id']);
            });

        $this->handler->handleCreatingMissingClients(new MissingClientsDetected($this->integration->id));
    }

    public function test_handle_creating_missing_clients_no_missing_realms(): void
    {
        $integrationId = Uuid::uuid4();

        $this->keycloakClientRepository->method('getMissingEnvironmentsByIntegrationId')
            ->with($integrationId)
            ->willReturn(new Environments());

        $this->logger->expects($this->once())
            ->method('info')
            ->with(sprintf('%s - already has all Keycloak clients', $integrationId));

        $this->handler->handleCreatingMissingClients(new MissingClientsDetected($integrationId));
    }
}
