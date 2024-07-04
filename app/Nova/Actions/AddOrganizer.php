<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Organizer;
use App\Domain\Integrations\Repositories\OrganizerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

final class AddOrganizer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(private readonly OrganizerRepository $organizerRepository)
    {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse
    {
        Log::info('AddOrganizer action started.');
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        /** @var string $organizerIdAsString */
        $organizerIdAsString = $fields->get('organizer_id');
        $organizerId = Uuid::fromString($organizerIdAsString);

        $this->organizerRepository->create(
            new Organizer(
                Uuid::uuid4(),
                Uuid::fromString($integration->id),
                $organizerId
            )
        );

        return Action::message('Organizer "' . $organizerIdAsString . '" added.');
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
}
