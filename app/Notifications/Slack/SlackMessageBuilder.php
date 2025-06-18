<?php

declare(strict_types=1);

namespace App\Notifications\Slack;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Notifications\MessageBuilder;
use App\Search\FetchNameForUdb3Organizer;
use App\Search\Sapi3\SearchService;

final readonly class SlackMessageBuilder implements MessageBuilder
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private FetchNameForUdb3Organizer $fetchNameForUdb3Organizer,
        private SearchService $searchService,
        private string $uitpasRootUri,
        private string $udbRootUri,
        private string $appUrl
    ) {
    }

    public function toMessage(Integration $integration): string
    {
        $integrationStatus = $integration->status;
        $subscription = $this->subscriptionRepository->getById($integration->subscriptionId);

        $message = '*' . $this->getStatusEmoji($integrationStatus) . ' ' . $integration->name . ' - ' . $this->getStatusText($integrationStatus) . '*';
        $message .= PHP_EOL . PHP_EOL;
        $message = $this->getBasicDetails($integration, $message);
        $message .= PHP_EOL . '• *Subscription:* _' . $subscription->name . '_';
        $message .= PHP_EOL . '• *Website:* _' . ($integration->website() ? $integration->website()->value : 'N/A') . '_';
        $message .= PHP_EOL;
        $message .= PHP_EOL . 'Open in publiq-platform: ' . $this->appUrl . '/admin/resources/integrations/' . $integration->id->toString();

        return $message;
    }

    public function toMessageWithOrganizer(Integration $integration, UdbOrganizer $udbOrganizer): string
    {
        $client = $integration->getKeycloakClientByEnv(Environment::Production);

        $organizerName = $this->fetchNameForUdb3Organizer->fetchName($this->searchService->findUiTPASOrganizers($udbOrganizer->organizerId));

        $message = '*:robot_face: :incoming_envelope: ' . $integration->name . ' - requested access to organisation ' . $organizerName . ' (' . $udbOrganizer->organizerId . ') *';
        $message .= PHP_EOL . PHP_EOL;
        $message = $this->getBasicDetails($integration, $message);
        $message .= PHP_EOL;
        $message .= PHP_EOL . '• *Open in publiq-platform:* ' . $this->appUrl . '/admin/resources/integrations/' . $integration->id->toString();
        $message .= PHP_EOL . '• *Open in UDB:* ' . $this->udbRootUri . 'organizers/' . $udbOrganizer->organizerId . '/preview';
        $message .= PHP_EOL . '• *Open in UiTPAS:* ' . $this->uitpasRootUri . $client->clientId;

        return $message;
    }

    private function getStatusEmoji(IntegrationStatus $integrationStatus): string
    {
        return match ($integrationStatus) {
            IntegrationStatus::Draft => ':tada:',
            IntegrationStatus::Active => ':white_check_mark:',
            IntegrationStatus::Blocked => ':no_entry:',
            IntegrationStatus::Deleted => ':x:',
            IntegrationStatus::PendingApprovalIntegration => ':hourglass_flowing_sand:',
            IntegrationStatus::PendingApprovalPayment => ':moneybag:',
        };
    }

    private function getStatusText(IntegrationStatus $integrationStatus): string
    {
        return match ($integrationStatus) {
            IntegrationStatus::Draft => 'Created',
            IntegrationStatus::Active => 'Activated',
            IntegrationStatus::Blocked => 'Blocked',
            IntegrationStatus::Deleted => 'Deleted',
            IntegrationStatus::PendingApprovalIntegration => 'Pending Approval',
            IntegrationStatus::PendingApprovalPayment => 'Pending Payment',
        };
    }

    private function getBasicDetails(Integration $integration, string $message): string
    {
        $message .= PHP_EOL . '• *Status:* _' . $integration->status->value . '_';
        $message .= PHP_EOL . '• *Description:* _' . $integration->description . '_';
        $message .= PHP_EOL . '• *Website:* _' . ($integration->website() ? $integration->website()->value : 'N/A') . '_';
        return $message;
    }
}
