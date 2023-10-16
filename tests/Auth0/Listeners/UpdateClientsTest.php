<?php

declare(strict_types=1);

namespace Tests\Auth0\Listeners;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Listeners\UpdateClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class UpdateClientsTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;

    private Auth0ClientRepository&MockObject $clientRepository;

    private IntegrationRepository&MockObject $integrationRepository;

    private UpdateClients $updateClients;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->clientRepository = $this->createMock(Auth0ClientRepository::class);

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);

        $this->updateClients = new UpdateClients(
            $this->createMockAuth0ClusterSDK($this->httpClient),
            $this->clientRepository,
            $this->integrationRepository,
            new NullLogger()
        );
    }

    public function test_it_updates_clients(): void
    {
        $integrationId = Uuid::uuid4();

        $integration = (new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Acceptance, IntegrationUrlType::Logout, 'https://www.publiq.be/logout'),
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Acceptance, IntegrationUrlType::Logout, 'https://www.madewithlove.be/logout'),
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Acceptance, IntegrationUrlType::Login, 'https://www.publiq.be/login'),
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Acceptance, IntegrationUrlType::Callback, 'https://www.publiq.be/callback'),
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Acceptance, IntegrationUrlType::Callback, 'https://www.madewithlove.be/callback'),
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Testing, IntegrationUrlType::Logout, 'https://www.madewithlove.be/logout'),
            new IntegrationUrl(Uuid::uuid4(), $integrationId, Environment::Production, IntegrationUrlType::Callback, 'https://www.publiq.be/callback'),
        );

        $clients = [
            new Auth0Client(Uuid::uuid4(), $integrationId, 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance),
            new Auth0Client(Uuid::uuid4(), $integrationId, 'client-id-2', 'client-secret-2', Auth0Tenant::Testing),
            new Auth0Client(Uuid::uuid4(), $integrationId, 'client-id-3', 'client-secret-3', Auth0Tenant::Production),
        ];

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

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
                        Json::encode([
                            'name' => 'Mock Integration (via publiq platform)',
                            'callbacks' => [
                                'https://www.publiq.be/callback',
                                'https://www.madewithlove.be/callback',
                                'https://oauth.pstmn.io/v1/callback',
                            ],
                            'allowed_logout_urls' => [
                                'https://www.publiq.be/logout',
                                'https://www.madewithlove.be/logout',
                            ],
                            'client_metadata' => [
                                'partner-status' => IntegrationPartnerStatus::THIRD_PARTY->value,
                            ],
                            'initiate_login_uri' => 'https://www.publiq.be/login',
                        ]),
                    ],
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-2',
                        Json::encode([
                            'name' => 'Mock Integration (via publiq platform)',
                            'callbacks' => ['https://oauth.pstmn.io/v1/callback'],
                            'allowed_logout_urls' => [
                                'https://www.madewithlove.be/logout',
                            ],
                            'client_metadata' => [
                                'partner-status' => IntegrationPartnerStatus::THIRD_PARTY->value,
                            ],
                        ]),
                    ],
                    [
                        'PATCH',
                        '/api/v2/clients/client-id-3',
                        Json::encode([
                            'name' => 'Mock Integration (via publiq platform)',
                            'callbacks' => [
                                'https://www.publiq.be/callback',
                                'https://oauth.pstmn.io/v1/callback',
                            ],
                            'allowed_logout_urls' => [],
                            'client_metadata' => [
                                'partner-status' => IntegrationPartnerStatus::THIRD_PARTY->value,
                            ],
                        ]),
                    ] => new Response(200, [], ''),
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->updateClients->handle(new IntegrationUpdated($integrationId));
    }
}
