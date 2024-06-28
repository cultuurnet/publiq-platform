<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Models\OrganizerModel;
use App\Domain\Integrations\Organizer;
use Illuminate\Support\Facades\DB;

final class EloquentOrganizerRepository implements OrganizerRepository
{
    public function create(Organizer ...$organizers): void
    {
        DB::transaction(function () use ($organizers): void {
            foreach ($organizers as $organizer) {
                OrganizerModel::query()->create([
                    'id' => $organizer->id->toString(),
                    'integration_id' => $organizer->integrationId->toString(),
                    'organizer_id' => $organizer->organizerId->toString(),
                ]);
            }
        });
    }
}
