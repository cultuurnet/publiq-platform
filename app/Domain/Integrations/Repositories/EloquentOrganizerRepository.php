<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Models\UiTdatabankOrganizerModel;
use App\Domain\Integrations\UiTdatabankOrganizer;
use Illuminate\Support\Facades\DB;

final class EloquentOrganizerRepository implements OrganizerRepository
{
    public function create(UiTdatabankOrganizer ...$organizers): void
    {
        DB::transaction(function () use ($organizers): void {
            foreach ($organizers as $organizer) {
                UiTdatabankOrganizerModel::query()->create([
                    'id' => $organizer->id->toString(),
                    'integration_id' => $organizer->integrationId->toString(),
                    'organizer_id' => $organizer->organizerId,
                ]);
            }
        });
    }

    public function delete(UiTdatabankOrganizer $organizer): void
    {
        UiTdatabankOrganizerModel::query()
            ->where('organizer_id', $organizer->organizerId)
            ->where('integration_id', $organizer->integrationId->toString())
            ->delete();
    }
}
