<?php

declare(strict_types=1);

namespace Tests\Nova\ActionGuards\UiTiDv1;

use App\Nova\ActionGuards\UiTiDv1\UnblockUiTiDv1ConsumerGuard;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1ConsumerStatus;
use App\UiTiDv1\UiTiDv1Environment;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\ClientInterface;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;
use Tests\UiTiDv1\CreatesMockUiTiDv1ClusterSDK;

final class UnblockUiTiDv1ConsumerGuardTest extends TestCase
{
    use CreatesMockUiTiDv1ClusterSDK;

    private ClientInterface&MockObject $httpClient;
    private UnblockUiTiDv1ConsumerGuard $guard;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->guard = new UnblockUiTiDv1ConsumerGuard(
            $this->createMockUiTiDv1ClusterSDK($this->httpClient)
        );
    }

    #[DataProvider('dataProvider')]
    public function test_can_do(Response $response, bool $expectedValue, string $message): void
    {
        $client = new UiTiDv1Consumer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'consumer-id-1',
            'consumer-key-1',
            'consumer-secret-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );

        $this->httpClient->expects($this->exactly(1))
            ->method('request')
            ->willReturn($response);

        $this->assertEquals($expectedValue, $this->guard->canDo($client), $message);
    }

    public static function dataProvider(): array
    {
        $activeXmlResponse = new SimpleXMLElement('<root></root>');
        $activeXmlResponse->addChild('status', UiTiDv1ConsumerStatus::Active->value);

        $blockedXmlResponse = new SimpleXMLElement('<root></root>');
        $blockedXmlResponse->addChild('status', UiTiDv1ConsumerStatus::Blocked->value);

        $emptyResponse = new SimpleXMLElement('<root></root>');

        return [
            [new Response(200, [], (string) $activeXmlResponse->asXML()), false, 'active response'],
            [new Response(200, [], (string) $blockedXmlResponse->asXML()), true, 'blocked response'],
            [new Response(200, [], (string) $emptyResponse->asXML()), false, 'empty body response'],
            [new Response(400), false, 'http request failed'],
        ];
    }
}
