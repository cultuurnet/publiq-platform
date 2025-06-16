<?php

declare(strict_types=1);

namespace Tests\Nova\Actions;

use App\Nova\Actions\AddUdbOrganizer;
use Tests\TestCase;
use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Laravel\Nova\Fields\ActionFields;

final class AddUdbOrganizerTest extends TestCase
{
    public function test_that_it_creates_a_UdbOrganizer(): void
    {
        $repository = $this->createMock(UdbOrganizerRepository::class);

        $action = new AddUdbOrganizer($repository);

        $integration = new IntegrationModel();
        $integration->id = '68498691-4ff0-8010-ae61-c1ece25eaf38';
        $organizerId = 'organizer-123';

        $repository->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($organizer) use ($integration, $organizerId) {
                return (string)$organizer->integrationId === $integration->id
                    && $organizer->organizerId === $organizerId;
            }));

        $fields = new ActionFields(collect(['organizer_id' => $organizerId]), collect());
        $integrations = new Collection([$integration]);

        $response = $action->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . $organizerId . '" added.', $json['message']);
    }

    public function test_it_handles_duplicates(): void
    {
        $repository = $this->createMock(UdbOrganizerRepository::class);

        $action = new AddUdbOrganizer($repository);

        $integration = new IntegrationModel();
        $integration->id = '68498691-4ff0-8010-ae61-c1ece25eaf38';
        $organizerId = 'organizer-123';

        $repository->expects($this->once())
            ->method('create')
            ->willThrowException(new \PDOException('Db is on fire! Duplicate found', 23000));

        $fields = new ActionFields(collect(['organizer_id' => $organizerId]), collect());
        $integrations = new Collection([$integration]);

        $response = $action->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . $organizerId . '" was already added.', $json['danger']);
        Event::assertNotDispatched(UdbOrganizerCreated::class);
    }
}
