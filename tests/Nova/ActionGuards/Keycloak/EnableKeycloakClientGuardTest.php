<?php

declare(strict_types=1);

namespace Tests\Nova\ActionGuards\Keycloak;

use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Client;
use App\Keycloak\Realm;
use App\Keycloak\Service\ApiClient;
use App\Nova\ActionGuards\Keycloak\EnableKeycloakClientGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class EnableKeycloakClientGuardTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ApiClient&MockObject $apiClient;
    private EnableKeycloakClientGuard $guard;
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->guard = new EnableKeycloakClientGuard(new CachedKeycloakClientStatus($this->apiClient, new NullLogger()));
        $this->client = new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-1', Realm::getMasterRealm());
    }

    #[DataProvider('dataProvider')]
    public function test_can_do(bool $isEnabled, bool $canEnable): void
    {
        $this->apiClient->expects($this->once())
            ->method('fetchIsClientEnabled')
            ->with($this->client->realm, $this->client->integrationId)
            ->willReturn($isEnabled);

        $this->assertEquals($canEnable, $this->guard->canDo($this->client));
    }

    public static function dataProvider(): array
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}