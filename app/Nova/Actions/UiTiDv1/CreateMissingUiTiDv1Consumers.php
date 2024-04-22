<?php

declare(strict_types=1);

namespace App\Nova\Actions\UiTiDv1;

use App\Domain\Integrations\Models\IntegrationModel;
use App\UiTiDv1\Jobs\CreateMissingConsumers as CreateMissingUiTiDv1ConsumersJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Event;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class CreateMissingUiTiDv1Consumers extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct()
    {
    }

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $integrationModel) {
            if (!$integrationModel instanceof IntegrationModel) {
                continue;
            }

            Event::dispatch(new CreateMissingUiTiDv1ConsumersJob(Uuid::fromString($integrationModel->id)));
        }
    }
}
