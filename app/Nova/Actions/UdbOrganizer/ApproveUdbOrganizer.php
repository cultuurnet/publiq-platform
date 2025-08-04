<?php

declare(strict_types=1);

namespace App\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

final class ApproveUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Approve UDB3 organizer request';

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

            $this->udbOrganizerRepository->updateStatus($udbOrganizer, UdbOrganizerStatus::Approved);
        }
    }
}
