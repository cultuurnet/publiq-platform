<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final readonly class UdbOrganizerCreated
{
    use Dispatchable;

    public function __construct(public UuidInterface $id)
    {
    }
}
