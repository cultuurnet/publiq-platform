<?php

declare(strict_types=1);

namespace Tests\Auth0\Listeners;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Listeners\UnblockClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;
use Tests\TestCase;

final class UnblockClientsTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;
    private const GRANTS = [
        'authorization_code',
        'refresh_token',
        'client_credentials',
    ];

    private ClientInterface&MockObject $httpClient;

    private Auth0ClientRepository&MockObject $clientRepository;

    private UnblockClients $unblockClients;

    public function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->clientRepository = $this->createMock(Auth0ClientRepository::class);

        $this->unblockClients = new UnblockClients(
            $this->createMockAuth0ClusterSDK($this->httpClient),
            $this->clientRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_unblocks_clients(): void
    {
        $integrationId = Uuid::uuid4();

        $clients = [
            new Auth0Client(Uuid::uuid4(), $integrationId, 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance),
            new Auth0Client(Uuid::uuid4(), $integrationId, 'client-id-2', 'client-secret-2', Auth0Tenant::Testing),
            new Auth0Client(Uuid::uuid4(), $integrationId, 'client-id-3', 'client-secret-3', Auth0Tenant::Production),
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
                        Json::encode(['grant_types' => self::GRANTS]),
                    ],
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-2',
                        Json::encode(['grant_types' => self::GRANTS]),
                    ],
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-3',
                        Json::encode(['grant_types' => self::GRANTS]),
                    ] => new Response(200, [], ''),
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->unblockClients->handle(new IntegrationUnblocked($integrationId));
    }
}
