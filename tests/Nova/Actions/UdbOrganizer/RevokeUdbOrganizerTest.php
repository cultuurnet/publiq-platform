<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Nova\Actions\UdbOrganizer\RevokeUdbOrganizer;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\CreatesTestData;
use Tests\GivenUitpasOrganizers;

final class RevokeUdbOrganizerTest extends TestCase
{
    use CreatesTestData;
    use GivenUitpasOrganizers;

    public function test_it_revokes_permissions_and_deletes_udb_organizer(): void
    {
        $organizerId = new UdbUuid(Uuid::uuid4()->toString());
        $integrationId = Uuid::uuid4();

        $udbOrganizer = new UdbOrganizerModel();
        $udbOrganizer->id = Uuid::uuid4()->toString();
        $udbOrganizer->integration_id = $integrationId->toString();
        $udbOrganizer->organizer_id = $organizerId->toString();
        $udbOrganizer->status = UdbOrganizerStatus::Pending->value;
        $udbOrganizer->client_id = Uuid::uuid4()->toString();
        $udbOrganizers = new Collection();
        $udbOrganizers->push($udbOrganizer);

        $udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);
        $udbOrganizerRepository
            ->expects($this->once())
            ->method('delete')
            ->with($integrationId, $organizerId);

        $handler = new RevokeUdbOrganizer($udbOrganizerRepository);

        $handler->handle(
            new ActionFields(collect(['organizer_id' => $organizerId->toString()]), collect()),
            new Collection([$udbOrganizer])
        );
    }
}
