<?php

declare(strict_types=1);

namespace App\Notifications\Slack;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Notifications\MessageBuilder;

final readonly class SlackMessageBuilder implements MessageBuilder
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private string $appUrl
    ) {
    }

    public function toMessage(Integration $integration): string
    {
        $integrationStatus = $integration->status;
        $subscription = $this->subscriptionRepository->getById($integration->subscriptionId);

        $message = '*' . $this->getStatusEmoji($integrationStatus) . ' ' . $integration->name . ' - ' . $this->getStatusText($integrationStatus) . '*';
        $message .= PHP_EOL . PHP_EOL;
        $message .= PHP_EOL . '• *Type:* _' . $integration->type->value . '_';
        $message .= PHP_EOL . '• *Status:* _' . $integration->status->value . '_';
        $message .= PHP_EOL . '• *Description:* _' . $integration->description . '_';
        $message .= PHP_EOL . '• *Subscription:* _' . $subscription->name . '_';
        $message .= PHP_EOL . '• *Website:* _' . ($integration->website() ? $integration->website()->value : 'N/A') . '_';
        $message .= PHP_EOL;
        $message .= PHP_EOL . 'Open in publiq-platform: ' . $this->appUrl . '/admin/resources/integrations/' . $integration->id->toString();

        return $message;
    }

    private function getStatusEmoji(IntegrationStatus $integrationStatus): string
    {
        return match ($integrationStatus) {
            IntegrationStatus::Draft => ':construction:',
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
}
