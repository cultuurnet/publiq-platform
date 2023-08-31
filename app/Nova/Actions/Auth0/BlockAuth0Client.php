<?php

declare(strict_types=1);

namespace App\Nova\Actions\Auth0;

use App\Auth0\Events\ClientBlocked;
use App\Auth0\Models\Auth0ClientModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class BlockAuth0Client extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $auth0ClientModel) {
            if (!$auth0ClientModel instanceof Auth0ClientModel) {
                continue;
            }

            ClientBlocked::dispatch(Uuid::fromString($auth0ClientModel->id));
        }
    }
}
