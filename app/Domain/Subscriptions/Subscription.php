<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions;

use App\Domain\Integrations\IntegrationType;
use Ramsey\Uuid\UuidInterface;

final class Subscription
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly string $name,
        public readonly string $description,
        public readonly IntegrationType $integrationType,
        public readonly Currency $currency,
        public readonly float $price,
        public readonly ?float $fee,
    ) {
    }
}
