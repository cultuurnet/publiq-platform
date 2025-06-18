<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Search\Sapi3\SearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use PDOException;
use Ramsey\Uuid\Uuid;

final class AddUdbOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly UdbOrganizerRepository $organizerRepository,
        private readonly SearchService $searchService
    ) {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse|Action
    {
        Log::info('AddUdbOrganizer action started.');
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        /** @var string $organizationIdAsString */
        $organizationIdAsString = $fields->get('organizer_id');

        if (!$this->doesOrganisationExistInUdb3($organizationIdAsString)) {
            return Action::danger('Organisation "' . $organizationIdAsString . '" not found in UDB3.');
        }

        try {
            $this->organizerRepository->create(
                new UdbOrganizer(
                    Uuid::uuid4(),
                    Uuid::fromString($integration->id),
                    $organizationIdAsString
                )
            );
        } catch (PDOException $e) {
            if ($e->getCode() === 23000) {
                // Handle integrity constraint violation
                return Action::danger('Organizer "' . $organizationIdAsString . '" was already added.');
            }

            return Action::danger($e->getMessage());
        }

        return Action::message('Organizer "' . $organizationIdAsString . '" added.');
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Organizer ID', 'organizer_id')
                ->rules(
                    'required',
                    'string'
                ),
        ];
    }

    private function doesOrganisationExistInUdb3(string $organizerId): bool
    {
        $result = $this->searchService->findUiTPASOrganizers($organizerId);
        return ($result->getTotalItems() >= 1);
    }
}
