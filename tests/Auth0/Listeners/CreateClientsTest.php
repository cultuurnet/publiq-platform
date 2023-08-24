<?php

declare(strict_types=1);

namespace Tests\Auth0\Listeners;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Listeners\CreateClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class CreateClientsTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;
    private IntegrationRepository&MockObject $integrationRepository;
    private Auth0ClientRepository&MockObject $clientRepository;
    private CreateClients $createClients;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->clientRepository = $this->createMock(Auth0ClientRepository::class);

        $this->createClients = new CreateClients(
            $this->createMockAuth0ClusterSDK($this->httpClient),
            $this->integrationRepository,
            $this->clientRepository,
            new NullLogger()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_new_client_in_every_configured_tenant(): void
    {
        $integrationId = Uuid::uuid4();
        $integration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $clientIds = [Uuid::uuid4(),Uuid::uuid4(),Uuid::uuid4()];

        $this->httpClient->expects($this->exactly(6))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                new Response(201, [], Json::encode(['id' => $clientIds[0], 'client_id' => 'client-id-1', 'client_secret' => 'client-secret-1'])),
                new Response(201, [], Json::encode(['id' => 'grant-id-1'])),
                new Response(201, [], Json::encode(['id' => $clientIds[1], 'client_id' => 'client-id-2', 'client_secret' => 'client-secret-2'])),
                new Response(201, [], Json::encode(['id' => 'grant-id-2'])),
                new Response(201, [], Json::encode(['id' => $clientIds[2], 'client_id' => 'client-id-3', 'client_secret' => 'client-secret-3'])),
                new Response(201, [], Json::encode(['id' => 'grant-id-3'])),
            );


        $expectedClients = [
            new Auth0Client($clientIds[0], $integrationId, 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance),
            new Auth0Client($clientIds[1], $integrationId, 'client-id-2', 'client-secret-2', Auth0Tenant::Testing),
            new Auth0Client($clientIds[2], $integrationId, 'client-id-3', 'client-secret-3', Auth0Tenant::Production),
        ];

        $this->clientRepository->expects($this->once())
            ->method('save')
            ->with(...$expectedClients);

        $this->createClients->handle(new IntegrationCreated($integrationId));
    }
}
