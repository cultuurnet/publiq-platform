<?php

declare(strict_types=1);

namespace Tests\Auth0;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\CachedAuth0ClientGrants;
use App\Json;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Psr\Http\Client\ClientInterface;
use Ramsey\Uuid\Uuid;

final class CachedAuth0ClientGrantsTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;
    private CachedAuth0ClientGrants $cachedAuth0ClientGrants;
    private Auth0Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->cachedAuth0ClientGrants = new CachedAuth0ClientGrants($this->createMockAuth0ClusterSDK($this->httpClient));

        $this->client = new Auth0Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance);
    }

    public function test_does_cache_layer_work(): void
    {
        $expectedGrants = ['foo', 'bar'];
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(
                new Response(200, [], Json::encode(['grant_types' => $expectedGrants]))
            );

        Log::shouldReceive('info')
            ->twice();

        $receivedGrants = $this->cachedAuth0ClientGrants->findGrantsOnClient($this->client);

        // Calling a second time to make sure the caching works, the API should only be requested once.
        $receivedGrants2 = $this->cachedAuth0ClientGrants->findGrantsOnClient($this->client);

        $this->assertEquals($expectedGrants, $receivedGrants);
        $this->assertEquals($expectedGrants, $receivedGrants2);
    }
}
