<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

final class InsightlyContact
{
    public function __construct(
        public readonly int $insightlyId,
        public readonly int $numberOfLinks,
    ) {
    }
}
