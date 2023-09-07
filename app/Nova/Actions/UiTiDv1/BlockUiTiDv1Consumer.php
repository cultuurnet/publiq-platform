<?php

declare(strict_types=1);

namespace App\Nova\Actions\UiTiDv1;

use App\UiTiDv1\Jobs\BlockConsumer;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class BlockUiTiDv1Consumer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        foreach ($actionModelCollection as $uiTiDv1ConsumerModel) {
            if (!$uiTiDv1ConsumerModel instanceof UiTiDv1ConsumerModel) {
                continue;
            }

            BlockConsumer::dispatch(Uuid::fromString($uiTiDv1ConsumerModel->id));
        }
    }
}
