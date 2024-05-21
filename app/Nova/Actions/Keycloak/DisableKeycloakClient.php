<?php

declare(strict_types=1);

namespace App\Nova\Actions\Keycloak;

use App\Keycloak\Jobs\DisableClient;
use App\Keycloak\Jobs\DisableClientHandler;
use App\Keycloak\Models\KeycloakClientModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;

final class DisableKeycloakClient extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Disable Keycloak client';

    public function __construct(private readonly Dispatcher $dispatcher, private readonly DisableClientHandler $listener)
    {
    }

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $clientModel) {
            if (!$clientModel instanceof KeycloakClientModel) {
                continue;
            }

            $this->dispatcher->dispatchSync(new DisableClient($clientModel->toDomain()->id), $this->listener);
        }
    }
}
