<?php

declare(strict_types=1);

namespace Tests\Search\UiTPAS;

use App\Json;
use App\Search\UiTPAS\HttpUiTPASLabelProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface;

final class HttpUiTPASLabelProviderTest extends TestCase
{
    public function test_it_returns_labels_on_success(): void
    {
        // link naar integratie toevoegen op admin overview

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(Json::encode(['uitpas-gent', 'uitpas-antwerpen']));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('critical');

        $provider = new HttpUiTPASLabelProvider(
            $client,
            $logger,
        );

        $labels = $provider->getLabels();

        $this->assertSame(['uitpas-gent', 'uitpas-antwerpen'], $labels);
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
}
