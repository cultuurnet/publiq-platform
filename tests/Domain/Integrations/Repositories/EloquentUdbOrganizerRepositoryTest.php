<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            Uuid::uuid4()->toString()
        );

        $this->organizer2 = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Uuid::uuid4()->toString()
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
        ]);
    }

    public function testCreateInBulk(): void
    {
        $organizers = new UdbOrganizerCollection([
            $this->organizer1,
            $this->organizer2,
        ]);

        $this->repository->createInBulk($organizers);

        foreach ($organizers as $organizer) {
            $this->assertDatabaseHas('udb_organizers', [
                'id' => $organizer->id->toString(),
                'integration_id' => $organizer->integrationId->toString(),
                'organizer_id' => $organizer->organizerId,
            ]);
        }
    }

    public function testDelete(): void
    {
        $repository = new EloquentUdbOrganizerRepository();

        $repository->delete($this->organizer1);

        $this->assertDatabaseMissing('udb_organizers', [
            'id' => $this->organizer1->id,
            'integration_id' => $this->organizer1->integrationId,
            'organizer_id' => $this->organizer1->organizerId,
        ]);
    }
}
