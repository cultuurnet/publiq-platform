<?php

declare(strict_types=1);

namespace App\Domain\Organizations;

use Ramsey\Uuid\UuidInterface;

final class Organization
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly string $name,
        public readonly string $invoiceEmail,
        public readonly ?string $vat,
        public readonly Address $address,
    ) {
    }
}
