<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\UiTPAS\Event\UdbOrganizerApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentUdbOrganizerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UdbOrganizer $organizer1;
    private UdbOrganizer $organizer2;
    private EloquentUdbOrganizerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer1 = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Pending
        );

        $this->organizer2 = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Pending
        );

        $this->repository = new EloquentUdbOrganizerRepository();
    }

    public function testCreate(): void
    {
        $this->repository->create($this->organizer1);

        $this->assertDatabaseHas('udb_organizers', [
            'id' => $this->organizer1->id->toString(),
            'integration_id' => $this->organizer1->integrationId->toString(),
            'organizer_id' => $this->organizer1->organizerId,
            'status' => UdbOrganizerStatus::Pending->value,
        ]);
    }

    public function testCreateWithDirectApproval(): void
    {
        $org = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Approved
        );

        $this->repository->create($org);

        $this->assertDatabaseHas('udb_organizers', [
            'id' => $org->id->toString(),
            'integration_id' => $org->integrationId->toString(),
            'organizer_id' => $org->organizerId,
            'status' => UdbOrganizerStatus::Approved->value,
        ]);

        Event::assertDispatched(UdbOrganizerApproved::class, static function (UdbOrganizerApproved $event) use ($org) {
            return $org->organizerId === $event->udbId &&
                $org->integrationId->toString() === $event->integrationId->toString();
        });
    }

    public function testCreateInBulk(): void
    {
        $organizers = new UdbOrganizers([
            $this->organizer1,
            $this->organizer2,
        ]);

        $this->repository->createInBulk($organizers);

        foreach ($organizers as $organizer) {
            $this->assertDatabaseHas('udb_organizers', [
                'id' => $organizer->id->toString(),
                'integration_id' => $organizer->integrationId->toString(),
                'organizer_id' => $organizer->organizerId,
                'status' => UdbOrganizerStatus::Pending->value,
            ]);
        }
    }

    public function testUpdateStatus(): void
    {
        $this->repository->create($this->organizer1);
        $this->repository->create($this->organizer2);

        $this->repository->updateStatus($this->organizer1, UdbOrganizerStatus::Approved);

        $this->assertDatabaseHas('udb_organizers', [
            'id' => $this->organizer1->id->toString(),
            'integration_id' => $this->organizer1->integrationId->toString(),
            'organizer_id' => $this->organizer1->organizerId,
            'status' => UdbOrganizerStatus::Approved->value,
        ]);

        $this->assertDatabaseHas('udb_organizers', [
            'id' => $this->organizer2->id->toString(),
            'integration_id' => $this->organizer2->integrationId->toString(),
            'organizer_id' => $this->organizer2->organizerId,
            'status' => UdbOrganizerStatus::Pending->value,
        ]);

        Event::assertDispatched(UdbOrganizerApproved::class);
    }

    public function testItCanDeleteAnUdbOrganizer(): void
    {
        $this->repository->create($this->organizer1);
        $this->repository->delete($this->organizer1->integrationId, $this->organizer1->organizerId);

        $this->assertDatabaseMissing('udb_organizers', [
            'id' => $this->organizer1->id,
            'integration_id' => $this->organizer1->integrationId,
            'organizer_id' => $this->organizer1->organizerId,
        ]);
    }

    public function testItCanGetAnUdbOrganizerById(): void
    {
        $this->repository->create($this->organizer1);

        $this->assertEquals($this->organizer1, $this->repository->getById($this->organizer1->id));
    }
}
