<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;

final readonly class UdbUuid
{
    // This is the regex also used in UDB3
    // Matches general UUID format but does not enforce UUIDv4 version or variant bits.
    private const UUID_REGEX = '/\A[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-?[0-9A-Fa-f]{12}\z/';

    public function __construct(public string $value)
    {
        if (!preg_match(self::UUID_REGEX, $value)) {
            throw new InvalidArgumentException("Invalid UUID format: {$value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
