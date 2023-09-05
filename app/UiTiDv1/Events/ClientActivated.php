<?php

declare(strict_types=1);

namespace App\UiTiDv1\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class ClientActivated
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
