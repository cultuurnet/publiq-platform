<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;

final class ApproveIntegration extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(private readonly IntegrationRepository $integrationRepository)
    {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        $this->integrationRepository->approve(Uuid::fromString($integration->id));

        return Action::message('Integration "' . $integration->name . '" approved.');
    }
}
