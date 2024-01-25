<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class DistributeKeys
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
}
