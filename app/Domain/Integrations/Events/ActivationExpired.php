<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class ActivationExpired
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
