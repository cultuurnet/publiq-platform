<?php

declare(strict_types=1);

namespace App\Nova\Actions\Auth0;

use App\Auth0\Jobs\BlockClient;
use App\Auth0\Jobs\BlockClientHandler;
use App\Auth0\Models\Auth0ClientModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class BlockAuth0Client extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Block Auth0 client';

    public function __construct(private readonly Dispatcher $dispatcher, private readonly BlockClientHandler $listener)
    {
    }

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $auth0ClientModel) {
            if (!$auth0ClientModel instanceof Auth0ClientModel) {
                continue;
            }

            $this->dispatcher->dispatchSync(new BlockClient(Uuid::fromString($auth0ClientModel->id)), $this->listener);
        }
    }
}
