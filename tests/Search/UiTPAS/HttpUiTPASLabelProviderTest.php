<?php

declare(strict_types=1);

namespace Tests\Search\UiTPAS;

use App\Json;
use App\Search\UiTPAS\HttpUiTPASLabelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface;

final class HttpUiTPASLabelProviderTest extends TestCase
{
    public function test_it_returns_labels_on_success(): void
    {
        $jsonResponse = Json::encode(['uitpas-gent', 'uitpas-antwerpen']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($this->createStreamMock($jsonResponse));

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('critical');

        $provider = new HttpUiTPASLabelProvider(
            $client,
            $logger,
        );

        $labels = $provider->getLabels();

        $this->assertSame(['labels:uitpas-gent', 'labels:uitpas-antwerpen'], $labels);
    }

    public function test_it_logs_and_returns_empty_array_on_failure(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with('Failed to fetch UiTPAS labels: 500');

        $provider = new HttpUiTPASLabelProvider(
            $client,
            $logger,
        );

        $labels = $provider->getLabels();

        $this->assertSame([], $labels);
    }

    private function createStreamMock(string $contents): StreamInterface&MockObject
    {
        $stream = $this->getMockBuilder(StreamInterface::class)->getMock();
        $stream->method('getContents')->willReturn($contents);
        return $stream;
    }
}
