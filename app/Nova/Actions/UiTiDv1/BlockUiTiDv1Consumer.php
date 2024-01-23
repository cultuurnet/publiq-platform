<?php

declare(strict_types=1);

namespace App\Nova\Actions\UiTiDv1;

use App\UiTiDv1\Jobs\BlockConsumer;
use App\UiTiDv1\Jobs\BlockConsumerHandler;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class BlockUiTiDv1Consumer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Block UiTiD v1 consumer';

    public function __construct(private readonly Dispatcher $dispatcher, private readonly BlockConsumerHandler $listener)
    {
    }

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $uiTiDv1ConsumerModel) {
            if (!$uiTiDv1ConsumerModel instanceof UiTiDv1ConsumerModel) {
                continue;
            }

            $this->dispatcher->dispatchSync(new BlockConsumer(Uuid::fromString($uiTiDv1ConsumerModel->id)), $this->listener);
        }
    }
}
