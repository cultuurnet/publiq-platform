<?php

declare(strict_types=1);

namespace App\Nova\Actions\UiTPAS;

use App\Domain\Integrations\Models\IntegrationModel;
use App\UiTPAS\SynchronizeUiTPASPermissionsHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;

final class SynchronizeUiTPASPermissions extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly SynchronizeUiTPASPermissionsHandler $handler,
    ) {

    }

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $integrationModel) {
            if (!$integrationModel instanceof IntegrationModel) {
                continue;
            }

            $result = $this->handler->handle($integrationModel->toDomain());

            if ($result->success === false) {
                Action::danger('Some permissions could not be restored for organizers: ' . implode(', ', $result->failedOrganizerIds));
                return;
            }
        }


        Action::message('UiTPAS permissions restored successfully.');
    }
}
