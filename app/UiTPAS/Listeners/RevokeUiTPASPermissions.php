<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTPAS\Event\UdbOrganizerDeleted;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class RevokeUiTPASPermissions implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTPASApiInterface $UiTPASApi,
        private readonly ClientCredentialsContext $prodContext,
        private readonly LoggerInterface $logger,
    ) {

    }

    public function handle(UdbOrganizerDeleted $event): void
    {
        $success = $this->UiTPASApi->deleteAllPermissions(
            $this->prodContext,
            $event->udbId,
            $this->integrationRepository
                ->getById($event->integrationId)
                ->getKeycloakClientByEnv($this->prodContext->environment)
                ->clientId
        );

        if (!$success) {
            $this->logger->critical(
                'Failed to add UiTPAS permissions for organizer - The UiTPAS API might be down',
                [
                    'organizer_id' => $event->udbId,
                    'integration_id' => $event->integrationId,
                ]
            );
        }
    }
}
