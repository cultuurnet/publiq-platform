<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Realm;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class CachedKeycloakClientStatusTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ApiClient&MockObject $apiClient;
    private CachedKeycloakClientStatus $cachedKeycloakClientStatus;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->cachedKeycloakClientStatus = new CachedKeycloakClientStatus($this->apiClient, new NullLogger());
        $this->client = new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-1', Realm::getMasterRealm());
    }

    public function test_does_cache_layer_work(): void
    {
        $this->apiClient->expects($this->once())
            ->method('fetchIsClientEnabled')
            ->with($this->client->realm, $this->client->integrationId)
            ->willReturn(true);

        $receivedGrants = $this->cachedKeycloakClientStatus->isClientEnabled($this->client);

        // Calling a second time to make sure the caching works, the API should only be requested once.
        $receivedGrants2 = $this->cachedKeycloakClientStatus->isClientEnabled($this->client);

        $this->assertTrue($receivedGrants);
        $this->assertTrue($receivedGrants2);
    }
}
