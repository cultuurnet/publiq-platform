<?php

declare(strict_types=1);

namespace Tests\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\Config;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Service\ApiClient;
use App\Keycloak\Service\CreateClientHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\IntegrationHelper;
use Tests\Keycloak\KeycloakHelper;

final class CreateClientHandlerTest extends TestCase
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

        $flow = new CreateClientHandler(
            $apiClient,
            $this->config,
            $this->scopeConfig,
            $this->createMock(LoggerInterface::class)
        );

        $clients = $flow->handle($this->integration);

        $this->assertCount($this->config->realms->count(), $clients);

        foreach ($clients as $i => $client) {
            $this->assertEquals($this->integration->id->toString(), $client->integrationId->toString());
            $this->assertEquals(self::SECRET, $client->clientSecret);
            $this->assertEquals($this->config->realms->get($i)?->internalName, $client->realm->internalName);
        }
    }
}
