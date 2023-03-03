<?php

declare(strict_types=1);

namespace Tests\Auth0\Listeners;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Listeners\BlockClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class BlockClientsTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private Auth0ClientRepository&MockObject $clientRepository;

    private BlockClients $blockClients;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->clientRepository = $this->createMock(Auth0ClientRepository::class);

        $this->blockClients = new BlockClients(
            $this->createMockAuth0ClusterSDK($this->httpClient),
            $this->clientRepository,
            new NullLogger()
        );
    }

    public function test_it_blocks_clients(): void
    {
        $integrationId = Uuid::uuid4();

        $clients = [
            new Auth0Client($integrationId, 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance),
            new Auth0Client($integrationId, 'client-id-2', 'client-secret-2', Auth0Tenant::Testing),
            new Auth0Client($integrationId, 'client-id-3', 'client-secret-3', Auth0Tenant::Production),
        ];

        $this->clientRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($clients);

        $this->httpClient->expects($this->exactly(3))
            ->method('sendRequest')
            ->willReturnCallback(
                fn (RequestInterface $request) =>
                match ([$request->getMethod(), $request->getUri()->getPath(), (string) $request->getBody()]) {
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-1',
                        Json::encode(['grant_types' => []]),
                    ],
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-2',
                        Json::encode(['grant_types' => []]),
                    ],
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-3',
                        Json::encode(['grant_types' => []]),
                    ] => new Response(200, [], ''),
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->blockClients->handle(new IntegrationBlocked($integrationId));
    }
}
