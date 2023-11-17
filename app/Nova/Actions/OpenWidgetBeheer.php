<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Facades\Session;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;

final class OpenWidgetBeheer extends Action
{
    public function handle(): ActionResponse|Action
    {
        $idToken = Session::get('id_token');
        return Action::redirect(config('project_aanvraag.base_uri') . '/session/?idToken=' . $idToken);
    }
}
