<?php

declare(strict_types=1);

namespace Tests\Nova\ActionGuards\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Nova\ActionGuards\Keycloak\UnblockKeycloakClientGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class UnblockKeycloakClientGuardTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;


    use RealmFactory;

    private ApiClient&MockObject $apiClient;
    private UnblockKeycloakClientGuard $guard;
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClient::class);
        $this->guard = new UnblockKeycloakClientGuard(new CachedKeycloakClientStatus($this->apiClient, new NullLogger()));
        $this->client = new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Acceptance);
    }

    #[DataProvider('dataProvider')]
    public function test_can_do(bool $isEnabled, bool $canEnable): void
    {
        $this->apiClient->expects($this->once())
            ->method('fetchIsClientActive')
            ->with($this->client)
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
