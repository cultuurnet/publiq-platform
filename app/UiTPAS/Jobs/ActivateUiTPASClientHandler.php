<?php

declare(strict_types=1);

namespace App\UiTPAS\Jobs;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class ActivateUiTPASClientHandler implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UdbOrganizerRepository $udbOrganizerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTPASApiInterface $api,
        private readonly ClientCredentialsContext $prodContext
    ) {
    }

    public function handle(ActivateUiTPASClient $event): void
    {
        $udbOrganizer = $this->udbOrganizerRepository->getById($event->id);

        //@todo should we return some feedback to user if call fails? Currently failures are only logged.
        $this->api->addPermissions(
            $this->prodContext,
            $udbOrganizer->organizerId,
            $this->integrationRepository
                ->getById($udbOrganizer->integrationId)
                ->getKeycloakClientByEnv($this->prodContext->environment)
                ->clientId
        );

        $this->udbOrganizerRepository->save($udbOrganizer->withStatus(UdbOrganizerStatus::Approved));
    }
}
