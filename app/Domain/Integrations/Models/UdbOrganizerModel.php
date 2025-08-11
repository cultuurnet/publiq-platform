<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Models\UuidModel;
use App\UiTPAS\Event\UdbOrganizerDeleted;
use App\UiTPAS\Event\UdbOrganizerRequested;
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
            static function (self $model) {
                UdbOrganizerCreated::dispatch(Uuid::fromString($model->id));
                if ($model->status === UdbOrganizerStatus::Pending->value) {
                    /* This event signals that an integrator has requested an organizer, as opposed to UdbOrganizerCreated, which is dispatched for every new organizer (regardless of status).
                    * The distinction allows handling different flows: "requested" (pending, by integrator) vs "approved" (created by admin in Nova).
                    */
                    UdbOrganizerRequested::dispatch(new UdbUuid($model->id), Uuid::fromString($model->integration_id));
                }
            },
        );
        self::deleted(
            static fn (self $model) => UdbOrganizerDeleted::dispatch(
                new UdbUuid($model->organizer_id),
                Uuid::fromString($model->integration_id)
            )
        );
    }
}
