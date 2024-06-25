<?php

declare(strict_types=1);

namespace App\UiTiDv1\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final readonly class ConsumerUnblocked
{
    use Dispatchable;

    public function __construct(public UuidInterface $id)
    {
    }
}
