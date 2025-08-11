<?php

declare(strict_types=1);

namespace App\UiTPAS\Event;

use App\Domain\UdbUuid;
use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

/* When handling this event, UdbOrganizer will be deleted from the DB, so we can't load it anymore.
 That's why both udbId and integrationId are included here.
*/
final class UdbOrganizerDeleted
{
    use Dispatchable;

    public function __construct(public UdbUuid $udbId, public UuidInterface $integrationId)
    {
    }
}
