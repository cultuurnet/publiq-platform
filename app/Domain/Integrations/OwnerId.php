<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

final class OwnerId
{
    public function __construct(
        public readonly string $id,
    ) {
    }
}
