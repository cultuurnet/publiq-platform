<?php

declare(strict_types=1);

namespace App\Domain\Coupons;

use Ramsey\Uuid\UuidInterface;

final class Coupon
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly bool $isDistributed,
        public readonly ?UuidInterface $integrationId,
        public readonly string $code,
    ) {
    }

    public function reduction(): float
    {
        return 125.0;
    }
}
