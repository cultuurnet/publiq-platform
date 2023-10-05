<?php

declare(strict_types=1);

namespace Tests\UiTiDv1;

use App\UiTiDv1\CachedUiTiDv1Status;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1ConsumerStatus;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\TestCaseWithDatabase;

final class CachedUiTiDv1StatusTest extends TestCaseWithDatabase
{
    use CreatesMockUiTiDv1ClusterSDK;
    use CreatesMockUiTiDv1Consumer;

    private ClientInterface&MockObject $httpClient;
    private CachedUiTiDv1Status $cachedUiTiDv1Status;
    private UiTiDv1Consumer $consumer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->cachedUiTiDv1Status = new CachedUiTiDv1Status($this->createMockUiTiDv1ClusterSDK($this->httpClient));

        $this->consumer = $this->createConsumer(Uuid::uuid4());
    }

    public function test_does_cache_layer_work(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn(
                new Response(200, [], '<root><status>ACTIVE</status></root>')
            );

        $receivedStatus = $this->cachedUiTiDv1Status->findStatusOnConsumer($this->consumer);

        // Calling a second time to make sure the caching works, the API should only be requested once.
        $receivedStatus2 = $this->cachedUiTiDv1Status->findStatusOnConsumer($this->consumer);

        $this->assertEquals(UiTiDv1ConsumerStatus::Active, $receivedStatus);
        $this->assertEquals(UiTiDv1ConsumerStatus::Active, $receivedStatus2);
    }
}
