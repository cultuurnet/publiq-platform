<?php

declare(strict_types=1);

namespace App\Nova\Actions\Keycloak;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Keycloak\Jobs\MissingClientsDetected;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Event;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class CreateMissingKeycloakClients extends Action
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

            Event::dispatch(new MissingClientsDetected(Uuid::fromString($integrationModel->id)));
        }
    }
}
