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
use App\Keycloak\Config;
use App\Keycloak\Listeners\UpdateClients;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Converters\IntegrationToKeycloakClientConverter;
use App\Keycloak\Converters\IntegrationUrlConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\Keycloak\KeycloakHttpClientFactory;

final class UpdateClientsTest extends TestCase
{
    use CreatesIntegration;
    use KeycloakHttpClientFactory;

    private const SECRET = 'my-secret';
    private const SEARCH_SCOPE_ID = '06059529-74b5-422a-a499-ffcaf065d437';

    private Integration $integration;
    private ScopeConfig $scopeConfig;
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
    }

    public function test_update_client_for_integration(): void
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
                ->method('updateClient')
                ->with(
                    $client,
                    array_merge(
                        IntegrationToKeycloakClientConverter::convert($client->id, $this->integration),
                        IntegrationUrlConverter::convert($this->integration, $client)
                    )
                );
            $this->apiClient->expects($this->once())
                ->method('deleteScopes')
                ->with($client);

            $this->apiClient->expects($this->once())
                ->method('addScopeToClient')
                ->with($client->realm, $client->id, Uuid::fromString(self::SEARCH_SCOPE_ID));

            $this->logger->expects($this->once())
                ->method('info')
                ->with('Keycloak client updated', [
                    'integration_id' => $this->integration->id->toString(),
                    'realm' => $client->realm->internalName,
                ]);

            $clients[] = $client;
        }

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($this->integration->id)
            ->willReturn($clients);

        $createClients = new UpdateClients(
            $integrationRepository,
            $keycloakClientRepository,
            $this->apiClient,
            $this->scopeConfig,
            $this->logger
        );

        $createClients->handle(new IntegrationUpdated($this->integration->id));
    }
}
