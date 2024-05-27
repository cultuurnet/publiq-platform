<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Models\OrganizerModel;
use App\Domain\Integrations\Organizer;

final class EloquentOrganizerRepository implements OrganizerRepository
{
    public function create(Organizer $organizer): void
    {
        OrganizerModel::query()->create([
            'id' => $organizer->id->toString(),
            'integration_id' => $organizer->integrationId->toString(),
            'organizer_id' => $organizer->organizerId->toString(),
        ]);
    }
}
