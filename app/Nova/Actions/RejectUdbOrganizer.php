<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
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
        /** @var UdbOrganizerModel $udbOrganizer */
        foreach ($udbOrganizers as $udbOrganizer) {
            $this->udbOrganizerRepository->delete($udbOrganizer->toDomain());
        }
    }
}
