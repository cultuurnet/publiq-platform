<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;

final class OpenWidgetBeheer extends Action
{
    public function handle(): ActionResponse|Action
    {
        return Action::redirect(config('widget_beheer.base_uri') . 1);
    }
}
