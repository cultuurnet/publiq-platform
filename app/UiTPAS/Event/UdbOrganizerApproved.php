<?php

declare(strict_types=1);

namespace App\UiTPAS\Event;

use App\Domain\UdbUuid;
use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class UdbOrganizerApproved
{
    use Dispatchable;

    public function __construct(public UdbUuid $udbId, public UuidInterface $integrationId)
    {
    }
}
