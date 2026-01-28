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
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\CreatesTestData;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class CreateClientsTest extends TestCase
{
    use CreatesTestData;

    use RealmFactory;

    private const SECRET = 'my-secret';

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

        $this->realms = $this->givenAllRealms();

        $this->handler = new CreateClients(
            $this->integrationRepository,
            $this->keycloakClientRepository,
            $this->realms,
            $this->apiClient,
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
                Uuid::uuid4()->toString(),
                self::SECRET,
                $realm->environment
            );
        }

        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('createClient')
            ->willReturnCallback(
                function (Realm $realm, Integration $integrationArgument) use ($clients) {
                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->internalName, $clients);

                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->internalName, $clients);

                    return $clients[$realm->internalName];
                }
            );

        $this->apiClient->expects($this->exactly($this->realms->count()))
            ->method('addScopeToClient')
            ->willReturnCallback(function (Client $client, UuidInterface $scopeId) {
                $this->assertEquals(Uuid::fromString(self::SEARCH_SCOPE_ID), $scopeId);
            });

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $this->keycloakClientRepository->expects($this->once())
            ->method('create')
            ->with(...array_values($clients));

        $this->logger->expects($this->exactly($this->realms->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('environment', $options);

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
                Uuid::uuid4()->toString(),
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

                    $this->assertEquals($this->integration->id, $integrationArgument->id);
                    $this->assertArrayHasKey($realm->environment->value, $clients);

                    return $clients[$realm->environment->value];
                }
            );

        $this->apiClient->expects($this->exactly($missingEnvironments->count()))
            ->method('addScopeToClient')
            ->willReturnCallback(function (Client $client, UuidInterface $scopeId) {
                $this->assertEquals(Uuid::fromString(self::SEARCH_SCOPE_ID), $scopeId);
            });

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $this->keycloakClientRepository->expects($this->once())
            ->method('create')
            ->with(...array_values($clients));

        $this->logger->expects($this->exactly($missingEnvironments->count()))
            ->method('info')
            ->willReturnCallback(function ($message, $options) {
                $this->assertEquals('Keycloak client created', $message);
                $this->assertArrayHasKey('integration_id', $options);
                $this->assertArrayHasKey('environment', $options);

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
