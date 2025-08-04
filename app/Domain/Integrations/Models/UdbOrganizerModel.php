<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Models\UuidModel;
use App\UiTPAS\Event\UdbOrganizerDeleted;
use Ramsey\Uuid\Uuid;

final class UdbOrganizerModel extends UuidModel
{
    protected $table = 'udb_organizers';

    protected $fillable = [
        'id',
        'integration_id',
        'organizer_id',
        'status',
    ];

    public function toDomain(): UdbOrganizer
    {
        return new UdbOrganizer(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            new UdbUuid($this->organizer_id),
            UdbOrganizerStatus::from($this->status)
        );
    }

    protected static function booted(): void
    {
        self::created(
            static fn (self $model) => UdbOrganizerCreated::dispatch(Uuid::fromString($model->id))
        );
        self::deleted(
            static fn (self $model) => UdbOrganizerDeleted::dispatch(
                new UdbUuid($model->organizer_id),
                Uuid::fromString($model->integration_id)
            )
        );
    }
}
