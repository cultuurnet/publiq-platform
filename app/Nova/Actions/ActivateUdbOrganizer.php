<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\UiTPAS\Jobs\ActivateUiTPASClient;
use App\UiTPAS\Jobs\ActivateUiTPASClientHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class ActivateUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Approve UDB3 organizer request';

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly ActivateUiTPASClientHandler $listener,
    ) {
    }

    public function handle(ActionFields $fields, Collection $udbOrganizers): void
    {
        foreach ($udbOrganizers as $udbOrganizer) {
            if (!$udbOrganizer instanceof UdbOrganizerModel) {
                continue;
            }

            $this->dispatcher->dispatchSync(new ActivateUiTPASClient(Uuid::fromString($udbOrganizer->id)), $this->listener);
        }
    }
}
