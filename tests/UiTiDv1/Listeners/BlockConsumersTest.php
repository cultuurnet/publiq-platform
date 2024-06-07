<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Listeners\BlockConsumers;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreateIntegration;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;

final class BlockConsumersTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;
    use CreateIntegration;

    private ClientInterface&MockObject $httpClient;
    private UiTiDv1ConsumerRepository&MockObject $consumerRepository;
    private IntegrationRepository&MockObject $integrationRepository;
    private BlockConsumers $blockConsumers;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->consumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->blockConsumers = new BlockConsumers(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->consumerRepository,
            $this->integrationRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public static function integrationEventsProvider(): \Generator
    {
        yield 'Integration blocked' => [new IntegrationBlocked(Uuid::uuid4())];
        yield 'Integration deleted' => [new IntegrationDeleted(Uuid::uuid4())];
    }

    #[dataProvider('integrationEventsProvider')]
    public function test_it_blocks_consumers(IntegrationBlocked|IntegrationDeleted $event): void
    {
        $integrationId = $event->id;

        $consumers = [
            new UiTiDv1Consumer(
                Uuid::uuid4(),
                $integrationId,
                '4135',
                'mock-consumer-key-1',
                'mock-consumer-secret-1',
                'mock-api-key-1',
                UiTiDv1Environment::Acceptance
            ),
            new UiTiDv1Consumer(
                Uuid::uuid4(),
                $integrationId,
                '4136',
                'mock-consumer-key-2',
                'mock-consumer-secret-2',
                'mock-api-key-2',
                UiTiDv1Environment::Testing
            ),
            new UiTiDv1Consumer(
                Uuid::uuid4(),
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

        $this->integrationRepository->expects($this->once())
            ->method('getByIdWithTrashed')
            ->willReturn($this->givenThereIsAnIntegration($integrationId));

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
                            'body' => 'status=BLOCKED&group=3',
                        ],
                    ],
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-2',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'status=BLOCKED&group=9',
                        ],
                    ],
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-3',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'status=BLOCKED&group=15',
                        ],
                    ] => new Response(200, [], ''),
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->blockConsumers->handle($event);
    }
}
