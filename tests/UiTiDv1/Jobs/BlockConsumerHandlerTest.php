<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Jobs;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Events\ConsumerBlocked;
use App\UiTiDv1\Jobs\BlockConsumer;
use App\UiTiDv1\Jobs\BlockConsumerHandler;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\CreateIntegration;
use Tests\TestCase;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;
use Tests\UiTiDv1\CreatesMockUiTiDv1Consumer;

final class BlockConsumerHandlerTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;
    use CreatesMockUiTiDv1Consumer;
    use CreateIntegration;

    private ClientInterface&MockObject $httpClient;
    private UiTiDv1ConsumerRepository&MockObject $clientRepository;
    private IntegrationRepository&MockObject $integrationRepository;
    private BlockConsumerHandler $blockClient;


    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->clientRepository = $this->createMock(UiTiDv1ConsumerRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->blockClient = new BlockConsumerHandler(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->clientRepository,
            $this->integrationRepository,
            new NullLogger()
        );
    }

    public function test_does_it_sent_an_activate_request(): void
    {
        $id = Uuid::uuid4();
        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($this->createConsumer($id));

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->givenThereIsAnIntegration($id));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(
                function ($method, $path, $options) {
                    return match ([$method, $path, $options['body'] ?? '']) {
                        [
                            'POST',
                            'serviceconsumer/consumer-key-1',
                            'status=BLOCKED&group=3',
                        ]
                        => new Response(200, [], ''),
                        default => throw new LogicException('Invalid arguments received'),
                    };
                }
            );

        $this->blockClient->handle(new BlockConsumer($id));

        Event::assertDispatched(ConsumerBlocked::class);
    }

    public function test_it_does_not_try_to_block_an_invalid_client(): void
    {
        $id = Uuid::uuid4();

        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willThrowException(new ModelNotFoundException());

        $this->httpClient->expects($this->exactly(0))
            ->method('request');

        $this->blockClient->handle(new BlockConsumer($id));

        Event::assertNotDispatched(ConsumerBlocked::class);
    }

    public function test_it_stops_on_invalid_request(): void
    {
        $id = Uuid::uuid4();
        $uiTiDv1Consumer = $this->createConsumer($id);
        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($uiTiDv1Consumer);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->givenThereIsAnIntegration($id));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn(
                new Response(400)
            );

        $this->blockClient->handle(new BlockConsumer($id));

        Event::assertNotDispatched(ConsumerBlocked::class);
    }
}
