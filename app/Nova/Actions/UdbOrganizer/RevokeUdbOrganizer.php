<?php

declare(strict_types=1);

namespace App\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

final class RevokeUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Revoke UDB3 organizer permissions';

    public function __construct(
        private readonly UdbOrganizerRepository $udbOrganizerRepository,
    ) {
    }

    public function handle(ActionFields $fields, Collection $udbOrganizers): void
    {
        foreach ($udbOrganizers as $udbOrganizerModel) {
            if (!$udbOrganizerModel instanceof UdbOrganizerModel) {
                continue;
            }

            $udbOrganizer = $udbOrganizerModel->toDomain();

            $this->udbOrganizerRepository->delete($udbOrganizer->integrationId, $udbOrganizer->organizerId);
        }
    }
}
