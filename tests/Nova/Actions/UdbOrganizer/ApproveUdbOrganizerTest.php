<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Nova\Actions\UdbOrganizer\ApproveUdbOrganizer;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class ApproveUdbOrganizerTest extends TestCase
{
    use CreatesIntegration;
    use GivenUitpasOrganizers;

    public function test_it_handles_activate_uitpas_client(): void
    {
        $udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);
        $handler = new ApproveUdbOrganizer($udbOrganizerRepository);

        $id = Uuid::uuid4();
        $integrationId = Uuid::uuid4();
        $organizerId = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';

        $udbOrganizer = new UdbOrganizerModel();
        $udbOrganizer->id = $id->toString();
        $udbOrganizer->integration_id = $integrationId->toString();
        $udbOrganizer->organizer_id = $organizerId;
        $udbOrganizer->status = UdbOrganizerStatus::Pending->value;
        $udbOrganizers = new Collection();
        $udbOrganizers->push($udbOrganizer);

        $udbOrganizerRepository->expects($this->once())
            ->method('updateStatus')
            ->with($udbOrganizer->toDomain(), UdbOrganizerStatus::Approved);

        $handler->handle(
            new ActionFields(collect(), collect()),
            $udbOrganizers
        );
    }
}
