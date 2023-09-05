<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Jobs;

use App\UiTiDv1\Events\ClientActivated;
use App\UiTiDv1\Jobs\ActivateClient;
use App\UiTiDv1\Jobs\ActivateClientListener;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;
use Tests\UiTiDv1\MockUitIdv1Consumer;

final class ActivateClientListenerTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;
    use MockUitIdv1Consumer;

    private ClientInterface&MockObject $httpClient;

    private UiTiDv1ConsumerRepository&MockObject $clientRepository;

    private ActivateClientListener $activateClient;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->clientRepository = $this->createMock(UiTiDv1ConsumerRepository::class);

        $this->activateClient = new ActivateClientListener(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient),
            $this->clientRepository,
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

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(
                function ($method, $path, $options) {
                    return match ([$method, $path, $options['body'] ?? '']) {
                        [
                            'POST',
                            'serviceconsumer/consumer-key-1',
                            'status=ACTIVE',
                        ]
                        => new Response(200, [], ''),
                        default => throw new LogicException('Invalid arguments received'),
                    };
                }
            );

        $this->activateClient->handle(new ActivateClient($id));

        Event::assertDispatched(ClientActivated::class);

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

        $this->activateClient->handle(new ActivateClient($id));

        Event::assertNotDispatched(ClientActivated::class);
    }

    public function test_it_stops_on_invalid_request(): void
    {
        $id = Uuid::uuid4();
        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($this->createConsumer($id));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn(
                new Response(400)
            );

        $this->activateClient->handle(new ActivateClient($id));

        Event::assertNotDispatched(ClientActivated::class);
    }
}
