<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerCollection;
use Illuminate\Support\Facades\DB;

final class EloquentUdbOrganizerRepository implements UdbOrganizerRepository
{
    public function create(UdbOrganizer $organizer): void
    {
        UdbOrganizerModel::query()->create([
            'id' => $organizer->id->toString(),
            'integration_id' => $organizer->integrationId->toString(),
            'organizer_id' => $organizer->organizerId,
        ]);
    }

    public function createInBulk(UdbOrganizerCollection $organizers): void
    {
        DB::transaction(function () use ($organizers): void {
            foreach ($organizers as $organizer) {
                UdbOrganizerModel::query()->create([
                    'id' => $organizer->id->toString(),
                    'integration_id' => $organizer->integrationId->toString(),
                    'organizer_id' => $organizer->organizerId,
                ]);
            }
        });
    }

    public function delete(UdbOrganizer $organizer): void
    {
        UdbOrganizerModel::query()
            ->where('organizer_id', $organizer->organizerId)
            ->where('integration_id', $organizer->integrationId->toString())
            ->delete();
    }
}
