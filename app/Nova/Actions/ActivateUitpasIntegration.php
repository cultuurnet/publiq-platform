<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\UdbUuid;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

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

        /** @var string $organizationIdAsString */
        $organizationIdAsString = $fields->get('organization');
        $organizationId = Uuid::fromString($organizationIdAsString);

        /** @var string $organizers */
        $organizers = $fields->get('organizers');

        $this->integrationRepository->activateWithOrganization(
            Uuid::fromString($integration->id),
            $organizationId,
            null,
            $this->getUdbOrganizers(
                $organizers,
                $this->integrationRepository->getById(Uuid::fromString($integration->id))
            )
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
            Text::make('Organizer(s)', 'organizers')
                ->rules(
                    'nullable',
                    'string'
                ),
        ];
    }

    private function getUdbOrganizers(string $organizers, Integration $integration): UdbOrganizers
    {
        $organizersAsIds = array_map('trim', explode(',', $organizers));
        $output = new UdbOrganizers();

        $productionClient = $integration->getKeycloakClientByEnv(Environment::Production);

        foreach ($organizersAsIds as $id) {
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
