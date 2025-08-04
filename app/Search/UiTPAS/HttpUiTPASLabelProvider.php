<?php

declare(strict_types=1);

namespace App\Search\UiTPAS;

use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

final readonly class HttpUiTPASLabelProvider implements UiTPASLabelProvider
{
    public function __construct(
        private ClientInterface $client,
        private LoggerInterface $logger,
    ) {
    }

    public function getLabels(): array
    {
        $response = $this->client->send(new Request(
            'GET',
            'uitpas/labels'
        ));

        if ($response->getStatusCode() !== 200) {
            $this->logger->critical('Failed to fetch UiTPAS labels: ' . $response->getStatusCode());
            return [];
        }

        return array_map(
            static fn (string $value) => 'labels:' . $value,
            Json::decodeAssociatively($response->getBody()->getContents())
        );
    }
}
