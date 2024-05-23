<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Config;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Service\ApiClient;
use App\Keycloak\Service\CreateClientForRealms;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\IntegrationHelper;

final class CreateClientsTest extends TestCase
{
    use IntegrationHelper;

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
        $this->config = new Config(
            true,
            'https://example.com/',
            'client_name',
            self::SECRET,
            RealmCollection::getRealms(),
        );

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
            new CreateClientForRealms($this->apiClient, $scopeConfig, $this->logger),
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

        $this->handler->handle(new IntegrationCreated($this->integration->id));

        foreach ($this->config->realms as $realm) {
            $this->assertArrayHasKey($realm->internalName, $realmHits, 'Client was not created for realm ' . $realm->internalName);
        }
    }
}
