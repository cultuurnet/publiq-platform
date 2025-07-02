<?php

declare(strict_types=1);

namespace App\Nova\Actions\UdbOrganizer;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

final class ActivateUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Approve UDB3 organizer request';

    public function __construct(
        private readonly UdbOrganizerRepository $udbOrganizerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTPASApiInterface $UiTPASApi,
        private readonly ClientCredentialsContext $prodContext
    ) {
    }

    public function handle(ActionFields $fields, Collection $udbOrganizers): void
    {
        foreach ($udbOrganizers as $udbOrganizerModel) {
            if (!$udbOrganizerModel instanceof UdbOrganizerModel) {
                continue;
            }

            $udbOrganizer = $udbOrganizerModel->toDomain();

            $success = $this->UiTPASApi->addPermissions(
                $this->prodContext,
                $udbOrganizer->organizerId,
                $this->integrationRepository
                    ->getById($udbOrganizer->integrationId)
                    ->getKeycloakClientByEnv($this->prodContext->environment)
                    ->clientId
            );

            if ($success) {
                $this->udbOrganizerRepository->updateStatus($udbOrganizer->id, UdbOrganizerStatus::Approved);

                UdbOrganizerApproved::dispatch($udbOrganizer->organizerId, $udbOrganizer->integrationId);
            }
        }
    }
}
