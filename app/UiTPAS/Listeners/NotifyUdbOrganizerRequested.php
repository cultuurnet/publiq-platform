<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Notifications\MessageBuilder;
use App\Notifications\Notifier;
use App\UiTPAS\Event\UdbOrganizerRequested;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class NotifyUdbOrganizerRequested implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly Notifier $notifier,
        private readonly MessageBuilder $messageBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handleUdbOrganizerRequested(UdbOrganizerRequested $event): void
    {
        $integration = $this->integrationRepository->getById($event->integrationId);
        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $udbOrganizer = $integration->getUdbOrganizerByOrgId($event->udbId);

        if ($udbOrganizer === null || $udbOrganizer->status !== UdbOrganizerStatus::Pending) {
            // In the case of an admin created organizer it will directly have status approved.
            return;
        }

        $this->notifier->postMessage($this->messageBuilder->toMessageWithOrganizer($integration, $udbOrganizer));
    }

    public function handleIntegrationActivationRequested(IntegrationActivationRequested $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);
        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $udbOrganizer =  $integration->udbOrganizers();

        foreach ($udbOrganizer as $organizer) {
            if ($organizer->status !== UdbOrganizerStatus::Pending) {
                // In the case of an admin created organizer it will directly have status approved.
                continue;
            }
            $this->notifier->postMessage($this->messageBuilder->toMessageWithOrganizer($integration, $organizer));
        }
    }

    public function failed(
        UdbOrganizerRequested|IntegrationActivationRequested $event,
        Throwable $throwable
    ): void {
        if ($event instanceof IntegrationActivationRequested) {
            $this->logger->error('Failed to notify about requested udb organizers', [
                'integration_id' => $event->id->toString(),
                'exception' => $throwable,
            ]);
            return;
        }

        $this->logger->error('Failed to notify about requested udb organizer', [
            'integration_id' => $event->integrationId->toString(),
            'udb_id' => $event->udbId->toString(),
            'exception' => $throwable,
        ]);
    }
}
