<?php

declare(strict_types=1);

namespace App\Auth0\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class ClientUnblocked
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
