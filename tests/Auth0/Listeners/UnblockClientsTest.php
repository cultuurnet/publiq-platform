<?php

namespace Tests\Auth0\Listeners;

use App\Auth0\Listeners\UnblockClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;
use Tests\TestCase;

class UnblockClientsTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

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
    }
}
