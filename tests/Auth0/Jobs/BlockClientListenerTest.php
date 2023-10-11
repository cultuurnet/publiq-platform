<?php

declare(strict_types=1);

namespace Tests\Auth0\Jobs;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Events\ClientBlocked;
use App\Auth0\Jobs\BlockClient;
use App\Auth0\Jobs\BlockClientListener;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Json;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;
use Tests\TestCase;

final class BlockClientListenerTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private Auth0ClientRepository&MockObject $clientRepository;

    private BlockClientListener $blockClients;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->clientRepository = $this->createMock(Auth0ClientRepository::class);

        $this->blockClients = new BlockClientListener(
            $this->createMockAuth0ClusterSDK($this->httpClient),
            $this->clientRepository,
            new NullLogger()
        );
    }

    public function test_does_it_sent_a_block_request(): void
    {
        $id = Uuid::uuid4();
        $client = new Auth0Client($id, Uuid::uuid4(), 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance);

        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($client);

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(
                fn (RequestInterface $request) => match ([$request->getMethod(), $request->getUri()->getPath(), (string)$request->getBody()]) {
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-1',
                        Json::encode(['grant_types' => []]),
                    ]
                    => new Response(200, [], ''),
                    default => throw new LogicException('Invalid arguments received'),
                }
            );

        $this->blockClients->handle(new BlockClient($id));

        Event::assertDispatched(ClientBlocked::class);
    }

    public function test_it_does_not_try_to_block_an_invalid_client(): void
    {
        $id = Uuid::uuid4();

        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willThrowException(new ModelNotFoundException());

        $this->httpClient->expects($this->exactly(0))
            ->method('sendRequest');

        $this->blockClients->handle(new BlockClient($id));
    }
}
