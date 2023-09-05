<?php

declare(strict_types=1);

namespace Tests\Auth0\Listeners\Client;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Jobs\ActivateClient;
use App\Auth0\Jobs\ClientActivatedListener;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Json;
use GuzzleHttp\Psr7\Response;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class ClientActivatedListenerTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private Auth0ClientRepository&MockObject $clientRepository;

    private ClientActivatedListener $activateClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->clientRepository = $this->createMock(Auth0ClientRepository::class);

        $this->activateClient = new ClientActivatedListener(
            $this->createMockAuth0ClusterSDK($this->httpClient),
            $this->clientRepository,
            new NullLogger()
        );
    }

    public function test_does_it_sent_an_activate_request(): void
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
                        Json::encode([
                            'grant_types' => [
                                'authorization_code',
                                'refresh_token',
                                'client_credentials',
                            ],
                        ]),
                    ]
                    => new Response(200, [], ''),
                    default => throw new LogicException('Invalid arguments received'),
                }
            );

        $this->activateClient->handle(new ActivateClient($id));
    }

    public function test_it_does_not_try_to_block_an_invalid_client(): void
    {
        $id = Uuid::uuid4();

        $this->clientRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn(null);

        $this->httpClient->expects($this->exactly(0))
            ->method('sendRequest');

        $this->activateClient->handle(new ActivateClient($id));
    }
}
