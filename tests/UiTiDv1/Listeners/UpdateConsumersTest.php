<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Listeners\UpdateConsumers;
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

final class UpdateConsumersTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private UiTiDv1ConsumerRepository&MockObject $consumerRepository;

    private IntegrationRepository&MockObject $integrationRepository;

    private UpdateConsumers $updateConsumers;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->consumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->updateConsumers = new UpdateConsumers(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->consumerRepository,
            $this->integrationRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_updates_consumers(): void
    {
        $integrationId = Uuid::uuid4();

        $integration = new Integration(
            $integrationId,
            IntegrationType::EntryApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

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

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

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
                            'body' => 'name=Mock%20Integration%20%28via%20publiq%20platform%29&group=1&group=2',
                        ],
                    ],
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-2',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'name=Mock%20Integration%20%28via%20publiq%20platform%29&group=7&group=8',
                        ],
                    ],
                    [
                        'POST',
                        'serviceconsumer/mock-consumer-key-3',
                        [
                            'http_errors' => false,
                            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                            'body' => 'name=Mock%20Integration%20%28via%20publiq%20platform%29&group=13&group=14',
                        ],
                    ] => new Response(200, [], ''),
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->updateConsumers->handle(new IntegrationUpdated($integrationId));
    }
}
