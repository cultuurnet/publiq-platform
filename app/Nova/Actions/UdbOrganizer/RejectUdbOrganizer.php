<?php

declare(strict_types=1);

namespace App\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\UiTPAS\Event\UdbOrganizerRejected;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

final class RejectUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Reject UDB3 organizer request';

    public function __construct(
        private readonly UdbOrganizerRepository $udbOrganizerRepository
    ) {
    }

    public function handle(ActionFields $fields, Collection $udbOrganizers): void
    {
        foreach ($udbOrganizers as $udbOrganizer) {
            if (!$udbOrganizer instanceof UdbOrganizerModel) {
                continue;
            }

            $udbOrganizerModel = $udbOrganizer->toDomain();
            $this->udbOrganizerRepository->delete($udbOrganizerModel->integrationId, $udbOrganizerModel->organizerId);
            UdbOrganizerRejected::dispatch($udbOrganizerModel->organizerId, $udbOrganizerModel->integrationId);
        }
    }
}
