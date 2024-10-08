<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Domain\Integrations\IntegrationStatus;
use App\Json;
use App\ProjectAanvraag\Requests\SyncWidgetRequest;
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
    public function syncWidget(SyncWidgetRequest $syncWidgetRequest): void
    {
        $baseUri = ProjectAanvraagUrl::getBaseUri();
        $request = new Request(
            'POST',
            $baseUri . 'project/' . $syncWidgetRequest->integrationId->toString(),
            [],
            Json::encode([
                'userId' => $syncWidgetRequest->userId,
                'name' => $syncWidgetRequest->name,
                'summary' => $syncWidgetRequest->summary,
                'groupId' => $syncWidgetRequest->groupId,
                'testApiKeySapi3' => $syncWidgetRequest->testApiKeySapi3,
                'liveApiKeySapi3' => $syncWidgetRequest->liveApiKeySapi3,
                'state' => $this->integrationStatusToWidgetStatus($syncWidgetRequest->status),
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

    private function integrationStatusToWidgetStatus(IntegrationStatus $status): string
    {
        return match ($status) {
            IntegrationStatus::Draft, IntegrationStatus::PendingApprovalIntegration => 'application_sent',
            IntegrationStatus::Active => 'active',
            IntegrationStatus::Blocked, IntegrationStatus::Deleted => 'blocked',
            IntegrationStatus::PendingApprovalPayment => 'waiting_for_payment',
        };
    }
}
