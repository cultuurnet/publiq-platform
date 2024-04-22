<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Json;
use App\ProjectAanvraag\Requests\CreateWidgetRequest;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

use JsonException;

final readonly class ProjectAanvraagClient
{
    public function __construct(
        private LoggerInterface  $logger,
        private ClientInterface $httpClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface|JsonException
     */
    public function createWidget(CreateWidgetRequest $createWidgetRequest): void
    {
        $baseUri = ProjectAanvraagUrl::getBaseUri();
        $request = new Request(
            'POST',
            $baseUri . '/project/' . $createWidgetRequest->integrationId->toString(),
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
