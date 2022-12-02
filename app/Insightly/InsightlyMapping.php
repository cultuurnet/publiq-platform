<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Insightly\Resources\ResourceType;
use Ramsey\Uuid\UuidInterface;

final class InsightlyMapping
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly int $insightlyId,
        public readonly ResourceType $resourceType
    ) {
    }
}
