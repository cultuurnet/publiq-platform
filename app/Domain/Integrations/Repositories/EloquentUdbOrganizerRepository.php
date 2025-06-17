<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentUdbOrganizerRepository implements UdbOrganizerRepository
{
    public function create(UdbOrganizer $organizer): void
    {
        UdbOrganizerModel::query()->create([
            'id' => $organizer->id->toString(),
            'integration_id' => $organizer->integrationId->toString(),
            'organizer_id' => $organizer->organizerId,
            'status' => $organizer->status->value,
        ]);
    }

    public function createInBulk(UdbOrganizers $organizers): void
    {
        DB::transaction(function () use ($organizers): void {
            foreach ($organizers as $organizer) {
                $this->create($organizer);
            }
        });
    }

    public function save(UdbOrganizer $organizer): void
    {
        UdbOrganizerModel::query()->updateOrCreate(
            [
                'id' => $organizer->id->toString(),
            ],
            [
                'id' => $organizer->id->toString(),
                'integration_id' => $organizer->integrationId->toString(),
                'organizer_id' => $organizer->organizerId,
                'status' => $organizer->status->value,
            ]
        );
    }

    public function delete(UdbOrganizer $organizer): void
    {
        UdbOrganizerModel::query()
            ->where('organizer_id', $organizer->organizerId)
            ->where('integration_id', $organizer->integrationId->toString())
            ->delete();
    }

    public function getById(UuidInterface $id): UdbOrganizer
    {
        /** @var UdbOrganizerModel $model */
        $model = UdbOrganizerModel::query()->findOrFail($id->toString());

        return $model->toDomain();
    }
}
