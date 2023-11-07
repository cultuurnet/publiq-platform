<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Json;
use App\ProjectAanvraag\Requests\CreateWidgetRequest;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final readonly class ProjectAanvraagClient
{
    public function __construct(
        private ClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
    }

    public function createWidget(CreateWidgetRequest $createWidgetRequest): void
    {
        $request = new Request(
            'POST',
            'project',
            [],
            Json::encode([
                'userId' => $createWidgetRequest->userId,
                'name' => $createWidgetRequest->name,
                'summary' => $createWidgetRequest->summary,
                'groupId' => $createWidgetRequest->groupId,
                'testApiKeySapi3' => $createWidgetRequest->testApiKeySapi3,
                'liveApiKeySapi3' => $createWidgetRequest->liveApiKeySapi3,
            ])
        );

        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(
                'Failed to create widget',
                [
                    'status_code' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                ]
            );
        } else {
            $this->logger->info(
                'Widget created',
                [
                    'status_code' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                ]
            );
        }
    }
}
