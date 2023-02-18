<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Listeners\CreateConsumers;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;

final class CreateConsumersTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;

    private ClientInterface&MockObject $httpClient;
    private IntegrationRepository&MockObject $integrationRepository;
    private UiTiDv1ConsumerRepository&MockObject $consumerRepository;
    private CreateConsumers $createConsumers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->consumerRepository = $this->createMock(UiTiDv1ConsumerRepository::class);

        $this->createConsumers = new CreateConsumers(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->integrationRepository,
            $this->consumerRepository,
            new NullLogger()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_new_consumer_in_every_configured_environment(): void
    {
        $integrationId = Uuid::uuid4();
        $integration = new Integration(
            $integrationId,
            IntegrationType::EntryApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $this->httpClient->expects($this->exactly(3))
            ->method('request')
            ->willReturnCallback(
                fn (string $actualMethod, string $actualUri, array $actualOptions) =>
                    match ([$actualMethod, $actualUri, $actualOptions]) {
                        [
                            'POST',
                            'serviceconsumer',
                            [
                                'http_errors' => false,
                                'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                                'body' => 'name=Mock%20Integration%20%28id%3A%20' . $integrationId . '%29&description=Mock%20description&group=1&group=2',
                            ],
                        ] => new Response(200, [], (string) file_get_contents(__DIR__ . '/consumer1.xml')),
                        [
                            'POST',
                            'serviceconsumer',
                            [
                                'http_errors' => false,
                                'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                                'body' => 'name=Mock%20Integration%20%28id%3A%20' . $integrationId . '%29&description=Mock%20description&group=7&group=8',
                            ],
                        ] => new Response(200, [], (string) file_get_contents(__DIR__ . '/consumer2.xml')),
                        [
                            'POST',
                            'serviceconsumer',
                            [
                                'http_errors' => false,
                                'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                                'body' => 'name=Mock%20Integration%20%28id%3A%20' . $integrationId . '%29&description=Mock%20description&group=13&group=14',
                            ],
                        ] => new Response(200, [], (string) file_get_contents(__DIR__ . '/consumer3.xml')),
                        default => throw new \LogicException('Invalid arguments received'),
                    }
            );

        $expectedConsumers = [
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
            ->method('save')
            ->with(...$expectedConsumers);

        $this->createConsumers->handle(new IntegrationCreated($integrationId));
    }
}
