<?php

declare(strict_types=1);

namespace App\Domain\Organizations;

use Ramsey\Uuid\UuidInterface;

final class Address
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $organizationId,
        public readonly string $street,
        public readonly string $zip,
        public readonly string $city,
        public readonly string $country
    ) {
    }
}
