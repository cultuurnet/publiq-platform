<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

final class UnblockIntegration extends Action
{
    public $name = 'Unblock Integration';

    public function handle(ActionFields $fields, Collection $integrations): void
    {
        /** @var IntegrationModel $integration */
        foreach ($integrations as $integration) {
            $integration->unblock();
        }
    }
}
