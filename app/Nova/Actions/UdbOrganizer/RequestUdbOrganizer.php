<?php

declare(strict_types=1);

namespace App\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Search\Sapi3\SearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use PDOException;
use Ramsey\Uuid\Uuid;

final class RequestUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly UdbOrganizerRepository $organizerRepository,
        private readonly SearchService $searchService,
        private readonly IntegrationRepository $integrationRepository,
    ) {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse|Action
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        try {
            $organizationId = new UdbUuid((string)$fields->get('organizer_id'));
        } catch (InvalidArgumentException) {
            return Action::danger('Invalid organizer ID.');
        }

        if (!$this->doesOrganizerExistInUdb($organizationId)) {
            return Action::danger('Organizer "' . $organizationId . '" not found in UDB3.');
        }

        try {
            $environment = Environment::from((string)$fields->get('environment'));
            $keycloakClient = $this->integrationRepository
                ->getById(Uuid::fromString($integration->id))
                ->getKeycloakClientByEnv($environment);

            $udbOrganizer = new UdbOrganizer(
                Uuid::uuid4(),
                Uuid::fromString($integration->id),
                $organizationId,
                UdbOrganizerStatus::Approved,
                $keycloakClient->id
            );

            $this->organizerRepository->create($udbOrganizer);
        } catch (PDOException $e) {
            if ($e->getCode() === 23000) {
                // Handle integrity constraint violation
                return Action::danger('Organizer "' . $organizationId . '" was already added.');
            }

            return Action::danger($e->getMessage());
        }

        return Action::message('Organizer "' . $organizationId . '" added.');
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Organizer ID', 'organizer_id')
                ->rules(
                    'required',
                    'string'
                ),
            Select::make('Environment', 'environment')
                ->options([
                    Environment::Testing->value => 'Test',
                    Environment::Production->value => 'Production',
                ])
                ->default(Environment::Production->value)
                ->rules('required')
                ->readonly(),
        ];
    }

    private function doesOrganizerExistInUdb(UdbUuid $organizerId): bool
    {
        $result = $this->searchService->findUiTPASOrganizers($organizerId);
        return ($result->getTotalItems() >= 1);
    }
}
