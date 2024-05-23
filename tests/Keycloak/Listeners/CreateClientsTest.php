<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\ClientCollection;
use App\Keycloak\Config;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Service\ApiClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\IntegrationHelper;
use Tests\Keycloak\KeycloakHelper;

final class CreateClientsTest extends TestCase
{
    use IntegrationHelper;
    use KeycloakHelper;

    private const SECRET = 'my-secret';
    private const SEARCH_SCOPE_ID = '06059529-74b5-422a-a499-ffcaf065d437';

    private Integration $integration;
    private ScopeConfig $scopeConfig;

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

        $this->scopeConfig = new ScopeConfig(
            Uuid::fromString(self::SEARCH_SCOPE_ID),
            Uuid::fromString('d8a54568-26da-412b-a441-d5e2fad84478'),
            Uuid::fromString('123ae05d-1c41-40c8-8716-c4654a3bfd98'),
            Uuid::fromString('0743b1c7-0ea2-46af-906e-fbb6c0317514'),
        );
    }

    public function test_create_client_for_integration(): void
    {
        $clientIds = [];

        $apiClient = $this->createMock(ApiClient::class);
        foreach ($this->config->realms as $realm) {
            $clientIds[$realm->internalName] = Uuid::uuid4();

            $apiClient->expects($this->once())
                ->method('addScopeToClient')
                ->with($realm, $clientIds[$realm->internalName], Uuid::fromString(self::SEARCH_SCOPE_ID));
        }

        $apiClient->expects($this->exactly($this->config->realms->count()))
            ->method('createClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clientIds) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);

                    if (!isset($clientIds[$realm->internalName])) {
                        $this->fail('Unknown realm, could not match with id: ' . $realm->internalName);
                    }

                    return $clientIds[$realm->internalName];
                }
            );

        $apiClient->expects($this->exactly($this->config->realms->count()))
            ->method('fetchClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integration) use ($clientIds) {
                    return new Client(
                        $clientIds[$realm->internalName],
                        $integration->id,
                        self::SECRET,
                        $realm
                    );
                }
            );

        $integrationRepository = $this->createMock(IntegrationRepository::class);
        $integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('create');

        //Check if clients where created for all realms
        $realmHits = [];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly($this->config->realms->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) use (&$realmHits) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('realm', $options);

                $this->assertEquals($this->integration->id->toString(), $options['integration_id']);

                $realmHits[$options['realm']] = true;
            });

        $createClients = new CreateClients(
            $integrationRepository,
            $keycloakClientRepository,
            $apiClient,
            $this->config,
            $this->scopeConfig,
            $logger
        );

        $createClients->handle(new IntegrationCreated($this->integration->id));

        foreach ($this->config->realms as $realm) {
            $this->assertArrayHasKey($realm->internalName, $realmHits, 'Client was not created for realm ' . $realm->internalName);
        }
    }
}
