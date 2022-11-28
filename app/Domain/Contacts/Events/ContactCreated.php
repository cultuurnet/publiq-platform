<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class ContactCreated
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
