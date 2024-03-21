<?php

declare(strict_types=1);

namespace App\Domain\Organizations;

use Ramsey\Uuid\UuidInterface;

final readonly class Organization
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
        public ?string $invoiceEmail,
        public ?string $vat,
        public Address $address,
    ) {
    }
}
