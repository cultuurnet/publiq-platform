<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Listeners;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Listeners\UnblockConsumers;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Tests\TestCase;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;

final class UnblockConsumersTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private UiTiDv1ConsumerRepository&MockObject $consumerRepository;

    private IntegrationRepository&MockObject $integrationRepository;
    private UnblockConsumers $unblockConsumers;

    public function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->consumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->unblockConsumers = new UnblockConsumers(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->consumerRepository,
            $this->integrationRepository,
            new NullLogger()
        );
    }

    public function test_it_can_unblock_consumers(): void
    {

    }
}
