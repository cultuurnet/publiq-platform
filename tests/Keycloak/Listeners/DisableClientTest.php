<?php

declare(strict_types=1);

namespace Tests\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Config;
use App\Keycloak\Listeners\DisableClients;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\KeycloakClientRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\IntegrationHelper;
use Tests\Keycloak\KeycloakHelper;

final class DisableClientTest extends TestCase
{
    use IntegrationHelper;
    use KeycloakHelper;

    private const SECRET = 'my-secret';

    private Integration $integration;
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

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_disable_clients_when_integration_is_blocked(): void
    {
        $integrationRepository = $this->createMock(IntegrationRepository::class);
        $integrationRepository->expects($this->once())
            ->method('getById')
            ->with($this->integration->id)
            ->willReturn($this->integration);

        $clients = [];
        foreach ($this->config->realms as $realm) {
            $client = new Client(Uuid::uuid4(), $this->integration->id, self::SECRET, $realm);

            $this->apiClient->expects($this->once())
                ->method('disableClient')
                ->with($client);

            $this->logger->expects($this->once())
                ->method('info')
                ->with('Keycloak client disabled', [
                    'integration_id' => $this->integration->id->toString(),
                    'client_id' => $client->id->toString(),
                    'realm' => $client->realm->internalName,
                ]);

            $clients[] = $client;
        }

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($this->integration->id)
            ->willReturn($clients);

        $createClients = new DisableClients(
            $integrationRepository,
            $keycloakClientRepository,
            $this->apiClient,
            $this->logger
        );

        $createClients->handle(new IntegrationBlocked($this->integration->id));
    }
}
