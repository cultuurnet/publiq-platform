<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\UiTdatabankOrganizer;
use App\Models\UuidModel;
use Ramsey\Uuid\Uuid;

final class UiTdatabankOrganizerModel extends UuidModel
{
    protected $table = 'organizers';

    protected $fillable = [
        'id',
        'integration_id',
        'organizer_id',
    ];

    public function toDomain(): UiTdatabankOrganizer
    {
        return new UiTdatabankOrganizer(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            $this->organizer_id,
        );
    }
}
