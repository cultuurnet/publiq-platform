<?php

declare(strict_types=1);

namespace Tests\Nova\ActionGuards\Auth0;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\CachedAuth0ClientGrants;
use App\Nova\ActionGuards\Auth0\ActivateAuth0ClientGuard;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Psr\Http\Client\ClientInterface;
use Ramsey\Uuid\Uuid;
use Tests\Auth0\CreatesMockAuth0ClusterSDK;

final class ActivateAuth0ClientGuardTest extends TestCase
{
    use CreatesMockAuth0ClusterSDK;

    private ClientInterface&MockObject $httpClient;
    private ActivateAuth0ClientGuard $activateAuth0ClientGuard;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->activateAuth0ClientGuard = new ActivateAuth0ClientGuard(
            new CachedAuth0ClientGrants($this->createMockAuth0ClusterSDK($this->httpClient))
        );
    }

    /** @dataProvider dataProvider */
    public function test_can_do(string $body, bool $expectedValue): void
    {
        $client = new Auth0Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-1', 'client-secret-1', Auth0Tenant::Acceptance);

        $this->httpClient->expects($this->exactly(1))
            ->method('sendRequest')
            ->willReturn(
                new Response(200, [], $body)
            );

        $this->assertEquals($expectedValue, $this->activateAuth0ClientGuard->canDo($client));
    }

    public static function dataProvider(): array
    {
        return [
            [json_encode(['grant_types' => ['test']]), false],
            [json_encode([]), true],
        ];
    }
}
