<?php

declare(strict_types=1);

namespace Tests\Auth0;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Auth0TenantSDK;
use App\Json;
use Auth0\SDK\Configuration\SdkConfiguration;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Psr\Http\Client\ClientInterface;
use Ramsey\Uuid\Uuid;

final class Auth0TenantSDKTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;

    private Auth0TenantSDK $auth0TenantSDK;
    private Auth0Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->auth0TenantSDK = new Auth0TenantSDK(
            Auth0Tenant::Acceptance,
            new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                domain: 'mock-acc.auth0.com',
                audience: ['https://mock.auth0.com/api/v2/'],
                httpClient: $this->httpClient,
                managementToken: 'mock-token',
            )
        );

        $this->client = new Auth0Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance);
    }

    public function test_find_grants_on_client(): void
    {
        $expectedGrants = ['foo', 'bar'];
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(
                new Response(200, [], Json::encode(['grant_types' => $expectedGrants]))
            );

        Log::shouldReceive('info')
            ->once();

        $receivedGrants = $this->auth0TenantSDK->findGrantsOnClient($this->client);

        $this->assertEquals($expectedGrants, $receivedGrants);
    }

    public function test_find_grants_on_client_does_faulty_response_work(): void
    {
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(
                new Response(200, [], '')
            );

        $this->assertEquals([], $this->auth0TenantSDK->findGrantsOnClient($this->client));
    }

    public function test_find_grants_on_client_does_400_response_work(): void
    {
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(
                new Response(400, [], '')
            );

        $this->assertEquals([], $this->auth0TenantSDK->findGrantsOnClient($this->client));
    }
}
