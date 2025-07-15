<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Models\OrganizationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

final class ActivateIntegration extends Action
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

        /** @var string $organizationIdAsString */
        $organizationIdAsString = $fields->get('organization');
        $organizationId = Uuid::fromString($organizationIdAsString);

        $this->integrationRepository->activateWithOrganization(
            Uuid::fromString($integration->id),
            $organizationId,
            null
        );

        return Action::message('Integration "' . $integration->name . '" activated.');
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Organization', 'organization')
                ->options(
                    OrganizationModel::query()->pluck('name', 'id')
                )
                ->rules(
                    'required',
                    'exists:organizations,id'
                ),
        ];
    }
}
