<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Notifications\MessageBuilder;
use App\Notifications\Notifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class NotifyUdbOrganizerRequested implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UdbOrganizerRepository $udbOrganizerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly Notifier $notifier,
        private readonly MessageBuilder $messageBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(UdbOrganizerCreated $event): void
    {
        $org = $this->udbOrganizerRepository->getById($event->id);
        $integration = $this->integrationRepository->getById($org->integrationId);

        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $this->notifier->postMessage($this->messageBuilder->toMessageWithOrganizer($integration, $org));
    }

    public function failed(
        UdbOrganizerCreated $event,
        Throwable $throwable
    ): void {
        $this->logger->error('Failed to notify about requested udb organizer', [
            'org_id' => $event->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
