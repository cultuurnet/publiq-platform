<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\UdbOrganizer;
use App\Models\UuidModel;
use Ramsey\Uuid\Uuid;

final class UdbOrganizerModel extends UuidModel
{
    protected $table = 'organizers';

    protected $fillable = [
        'id',
        'integration_id',
        'organizer_id',
    ];

    public function toDomain(): UdbOrganizer
    {
        return new UdbOrganizer(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            $this->organizer_id,
        );
    }
}
