<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Organizer;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\OrganizerRepository;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Search\Sapi3\SearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\MultiselectField\Multiselect as Outl1neMultiselect;
use Ramsey\Uuid\Uuid;

final class ActivateUitpasIntegration extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly OrganizerRepository $organizerRepository,
        private readonly SearchService $searchService
    ) {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        /** @var string $organizationIdAsString */
        $organizationIdAsString = $fields->get('organization');
        $organizationId = Uuid::fromString($organizationIdAsString);

        /** @var string $organizersAsString */
        $organizersAsString = $fields->get('organizers');
        $organizerId = Uuid::fromString($organizersAsString);

        $this->integrationRepository->activateWithOrganization(
            Uuid::fromString($integration->id),
            $organizationId,
            null
        );

        $this->organizerRepository->create(
            new Organizer(
                Uuid::uuid4(),
                Uuid::fromString($integration->id),
                $organizerId
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
            Outl1neMultiselect::make('UiTPAS Organizer(s)')
                ->options(
                    function (string $input = '') {
                        $organizers = $this->searchService->searchUiTPASOrganizer($input);

                        $collection = [];
                        if ($organizers->getMember() === null) {
                            return $collection;
                        }

                        /** @var \CultuurNet\SearchV3\ValueObjects\Organizer $organizer */
                        foreach ($organizers->getMember()->getItems() as $organizer) {
                            if ($organizer->getName() === null) {
                                continue;
                            }
                            $collection[$organizer->getId()] = $organizer->getName()->getValueForLanguage('nl');
                        }
                        return $collection;
                    }
                )
                ->optionsLimit(5),
        ];
    }
}
