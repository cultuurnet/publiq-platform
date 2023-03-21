<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\UiTiDv1\Listeners\BlockConsumers;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;

final class BlockConsumersTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private UiTiDv1ConsumerRepository&MockObject $consumerRepository;

    private BlockConsumers $blockConsumers;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->consumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);

        $this->blockConsumers = new BlockConsumers(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->consumerRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_blocks_consumers(): void
    {
        $integrationId = Uuid::uuid4();

        $consumers = [
            new UiTiDv1Consumer(
                $integrationId,
                '4135',
                'mock-consumer-key-1',
                'mock-consumer-secret-1',
                'mock-api-key-1',
                UiTiDv1Environment::Acceptance
            ),
            new UiTiDv1Consumer(
                $integrationId,
                '4136',
                'mock-consumer-key-2',
                'mock-consumer-secret-2',
                'mock-api-key-2',
                UiTiDv1Environment::Testing
            ),
            new UiTiDv1Consumer(
                $integrationId,
                '4137',
                'mock-consumer-key-3',
                'mock-consumer-secret-3',
                'mock-api-key-3',
                UiTiDv1Environment::Production
            ),
        ];

        $this->consumerRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($consumers);

        $this->httpClient->expects($this->exactly(3))
            ->method('request')
            ->willReturnCallback(
                fn (string $actualMethod, string $actualUri, array $actualOptions) =>
                match ([$actualMethod, $actualUri, $actualOptions]) {
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-1',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'status=BLOCKED',
                        ],
                    ],
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-2',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'status=BLOCKED',
                        ],
                    ],
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-3',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'status=BLOCKED',
                        ],
                    ] => new Response(200, [], ''),
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->blockConsumers->handle(new IntegrationBlocked($integrationId));
    }
}
