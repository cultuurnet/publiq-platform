<?php

declare(strict_types=1);

namespace App\Domain\Histories;

use Illuminate\Support\Carbon;
use Ramsey\Uuid\UuidInterface;

final class History
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $itemId,
        public readonly string $userId,
        public readonly string $model,
        public readonly string $event,
        public readonly Carbon $timestamp,
    ) {
    }
}
