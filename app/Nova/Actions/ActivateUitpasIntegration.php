<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\UdbUuid;
use App\Nova\Resources\Organization as OrganizationResource;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ActivateUitpasIntegration extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository
    ) {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        /** @var OrganizationModel $organization */
        $organization = $fields->get('organization');
        $organizationId = Uuid::fromString($organization->id);

        /** @var ?string $organizers */
        $organizers = $fields->get('organizers');

        $integrationId = Uuid::fromString($integration->id);

        $this->integrationRepository->activateWithOrganization(
            $integrationId,
            $organizationId,
            null,
            $this->getUdbOrganizers($organizers, $integrationId)
        );

        return Action::message('Integration "' . $integration->name . '" activated.');
    }

    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Organization', 'organization', OrganizationResource::class)
                ->searchable()
                ->withoutTrashed()
                ->rules(
                    'required',
                    Rule::exists('organizations', 'id')->whereNull('deleted_at')
                ),
            Text::make('Organizer(s)', 'organizers')
                ->rules(
                    'nullable',
                    'string'
                ),
        ];
    }

    private function getUdbOrganizers(?string $organizers, UuidInterface $integrationId): UdbOrganizers
    {
        $organizerIds = array_filter(array_map('trim', explode(',', $organizers ?? '')));

        if ($organizerIds === []) {
            return new UdbOrganizers();
        }

        $integration = $this->integrationRepository->getById($integrationId);
        $productionClient = $integration->getKeycloakClientByEnv(Environment::Production);

        $output = new UdbOrganizers();

        foreach ($organizerIds as $id) {
            $output->add(
                new UdbOrganizer(
                    Uuid::uuid4(),
                    $integration->id,
                    new UdbUuid($id),
                    UdbOrganizerStatus::Pending,
                    $productionClient->id
                )
            );
        }

        return $output;
    }
}
