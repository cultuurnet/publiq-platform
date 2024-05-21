<?php

declare(strict_types=1);

namespace App\Keycloak\Jobs;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class EnableClient
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
