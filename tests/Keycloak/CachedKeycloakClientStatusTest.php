<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;
use Tests\TestCase;

final class CachedKeycloakClientStatusTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;


    use RealmFactory;

    private ApiClient&MockObject $apiClient;
    private CachedKeycloakClientStatus $cachedKeycloakClientStatus;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->cachedKeycloakClientStatus = new CachedKeycloakClientStatus($this->apiClient, new NullLogger());
        $this->client = new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), 'client-id-1', Environment::Acceptance);
    }

    public function test_does_cache_layer_work(): void
    {
        $this->apiClient->expects($this->once())
            ->method('fetchIsClientActive')
            ->with($this->client)
            ->willReturn(true);

        $receivedGrants = $this->cachedKeycloakClientStatus->isClientBlocked($this->client);

        // Calling a second time to make sure the caching works, the API should only be requested once.
        $receivedGrants2 = $this->cachedKeycloakClientStatus->isClientBlocked($this->client);

        $this->assertFalse($receivedGrants);
        $this->assertFalse($receivedGrants2);
    }
}
