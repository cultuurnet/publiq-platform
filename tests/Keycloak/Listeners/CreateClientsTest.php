<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\ClientCollection;
use App\Keycloak\Config;
use App\Keycloak\Events\MissingClientsDetected;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\Keycloak\ConfigFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class CreateClientsTest extends TestCase
{
    use CreatesIntegration;
    use ConfigFactory;
    use RealmFactory;


    private const SECRET = 'my-secret';
    private const SEARCH_SCOPE_ID = '06059529-74b5-422a-a499-ffcaf065d437';

    private Config $config;
    private Integration $integration;
    private CreateClients $handler;
    private IntegrationRepository&MockObject $integrationRepository;
    private KeycloakClientRepository&MockObject $keycloakClientRepository;
    private ApiClient&MockObject $apiClient;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->config = $this->givenKeycloakConfig();

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

        $this->handler = new CreateClients(
            $this->integrationRepository,
            $this->keycloakClientRepository,
            $this->config,
            $this->apiClient,
            $scopeConfig,
            $this->logger,
        );
    }

    public function test_create_client_for_integration(): void
    {
        $clients = [];

        foreach ($this->config->realms as $realm) {
            $clients[$realm->internalName] = new Client(
                Uuid::uuid4(),
                $this->integration->id,
                Uuid::uuid4(),
                self::SECRET,
                $realm
            );

            $this->apiClient->expects($this->once())
                ->method('addScopeToClient')
                ->with($realm, $clients[$realm->internalName]->id, Uuid::fromString(self::SEARCH_SCOPE_ID));
        }

        $this->apiClient->expects($this->exactly($this->config->realms->count()))
            ->method('createClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clients) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->internalName, $clients);

                    return $clients[$realm->internalName]->id;
                }
            );

        $this->apiClient->expects($this->exactly($this->config->realms->count()))
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

        //Check if clients where created for all realms
        $realmHits = [];

        $this->logger->expects($this->exactly($this->config->realms->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) use (&$realmHits) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('realm', $options);

                $this->assertEquals($this->integration->id->toString(), $options['integration_id']);

                $realmHits[$options['realm']] = true;
            });

        $this->handler->handleCreateClients(new IntegrationCreated($this->integration->id));

        foreach ($this->config->realms as $realm) {
            $this->assertArrayHasKey($realm->internalName, $realmHits, 'Client was not created for realm ' . $realm->internalName);
        }
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
        $integrationId = Uuid::uuid4();
        $missingRealms = $this->config->realms;
        $integration = $this->givenThereIsAnIntegration($integrationId);

        $this->keycloakClientRepository->expects($this->once())
            ->method('getMissingRealmsByIntegrationId')
            ->with($integrationId)
            ->willReturn($missingRealms);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $clientIds = [];
        foreach ($missingRealms as $realm) {
            $clientIds[$realm->internalName] = Uuid::uuid4();

            $this->apiClient->expects($this->once())
                ->method('addScopeToClient')
                ->with($realm, $clientIds[$realm->internalName], Uuid::fromString(self::SEARCH_SCOPE_ID));
        }

        $this->apiClient->expects($this->exactly($missingRealms->count()))
            ->method('createClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clientIds, $integration) {
                    $this->assertEquals($integration->id, $integrationArgument->id);

                    if (!isset($clientIds[$realm->internalName])) {
                        $this->fail('Unknown realm, could not match with id: ' . $realm->internalName);
                    }

                    return $clientIds[$realm->internalName];
                }
            );

        $clients = new ClientCollection();

        $this->apiClient->expects($this->exactly($missingRealms->count()))
            ->method('fetchClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integration) use ($clients, $clientIds) {
                    $client = new Client(
                        $clientIds[$realm->internalName],
                        $integration->id,
                        Uuid::uuid4(),
                        self::SECRET,
                        $realm
                    );
                    $clients->add($client);
                    return $client;
                }
            );

        $this->keycloakClientRepository->expects($this->once())
            ->method('create')
            ->with(... $clients);

        $this->logger->expects($this->exactly($missingRealms->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) use ($integration, $clientIds) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('realm', $options);
                $this->assertArrayHasKey('client_id', $options);

                $this->assertEquals($integration->id->toString(), $options['integration_id']);
                $this->assertEquals($clientIds[$options['realm']], $options['client_id']);
            });

        $this->handler->handleCreatingMissingClients(new MissingClientsDetected($integrationId));
    }

    public function test_handle_creating_missing_clients_no_missing_realms(): void
    {
        $integrationId = Uuid::uuid4();

        $this->keycloakClientRepository->method('getMissingRealmsByIntegrationId')
            ->with($integrationId)
            ->willReturn(new RealmCollection());

        $this->logger->expects($this->once())
            ->method('info')
            ->with(sprintf('%s - already has all Keycloak clients', $integrationId));

        $this->handler->handleCreatingMissingClients(new MissingClientsDetected($integrationId));
    }
}
