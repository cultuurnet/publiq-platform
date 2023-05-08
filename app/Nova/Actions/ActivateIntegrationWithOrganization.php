<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

final class ActivateIntegrationWithOrganization extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly IntegrationRepository $integrationRepository,
        public readonly OrganizationRepository $organizationRepository
    ) {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse
    {
        /** @var string $organizationIdAsString */
        $organizationIdAsString = $fields->get('organization');
        $organizationId = Uuid::fromString($organizationIdAsString);

        $organization = $this->organizationRepository->getById($organizationId);

        /** @var IntegrationModel $integration */
        $integration = $integrations->first();
        $this->integrationRepository->activateWithOrganization(
            Uuid::fromString($integration->id),
            $organizationId
        );

        return Action::message(
            'Integration ' . $integration->name . ' activated with organization ' . $organization->name
        );
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Organization', 'organization')->options(
                OrganizationModel::query()->pluck('name', 'id')
            ),
        ];
    }
}
