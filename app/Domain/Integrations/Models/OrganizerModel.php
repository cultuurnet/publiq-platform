<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\Organizer;
use App\Models\UuidModel;
use Ramsey\Uuid\Uuid;

final class OrganizerModel extends UuidModel
{
    protected $table = 'organizers';

    protected $fillable = [
        'id',
        'integration_id',
        'organizer_id',
    ];

    public function toDomain(): Organizer
    {
        return new Organizer(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            Uuid::fromString($this->organizer_id),
        );
    }
}
