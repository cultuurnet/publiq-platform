<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

final class OpenWidgetBeheer extends Action
{
    public function handle(ActionFields $fields, Collection $integrations): ActionResponse|Action
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();
        $idToken = Session::get('id_token');
        return Action::redirect(config('project_aanvraag.base_uri') . 'project/' . $integration->id . '/widget/?idToken=' . $idToken);
    }
}
