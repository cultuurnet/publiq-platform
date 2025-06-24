<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentUdbOrganizerRepository implements UdbOrganizerRepository
{
    public function create(UdbOrganizer $organizer): void
    {
        UdbOrganizerModel::query()->create([
            'id' => $organizer->id->toString(),
            'integration_id' => $organizer->integrationId->toString(),
            'organizer_id' => $organizer->organizerId->toString(),
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

    public function updateStatus(UuidInterface $id, UdbOrganizerStatus $newStatus): void
    {
        UdbOrganizerModel::query()->update(
            [
                'id' => $id->toString(),
                'status' => $newStatus->value,
            ]
        );
    }

    public function delete(UuidInterface $integrationId, UdbUuid $organizerId): void
    {
        UdbOrganizerModel::query()
            ->where('integration_id', $integrationId->toString())
            ->where('organizer_id', $organizerId->toString())
            ->delete();
    }

    public function getById(UuidInterface $id): UdbOrganizer
    {
        /** @var UdbOrganizerModel $model */
        $model = UdbOrganizerModel::query()->findOrFail($id->toString());

        return $model->toDomain();
    }
}
