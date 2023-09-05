<?php

declare(strict_types=1);

namespace App\Auth0\Jobs;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class ActivateClient
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
