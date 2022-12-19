<?php

declare(strict_types=1);

namespace App\Domain\Organizations;

final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $zip,
        public readonly string $city,
        public readonly string $country
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->street === '' && $this->zip === '' && $this->city === '' && $this->country === '';
    }
}
