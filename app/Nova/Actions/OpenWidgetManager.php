<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\ProjectAanvraag\ProjectAanvraagUrl;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

final class OpenWidgetManager extends Action
{
    public $name = 'Open Widget Beheer';

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse|Action
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();
        $url = ProjectAanvraagUrl::getForIntegration($integration->toDomain());

        return Action::redirect($url);
    }
}
