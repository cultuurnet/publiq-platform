<?php

declare(strict_types=1);

namespace App\Keycloak\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final readonly class ClientCreated
{
    use Dispatchable;

    public function __construct(public UuidInterface $id)
    {
    }
}
