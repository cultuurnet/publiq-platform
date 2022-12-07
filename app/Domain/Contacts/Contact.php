<?php

declare(strict_types=1);

namespace App\Domain\Contacts;

use Ramsey\Uuid\UuidInterface;

final class Contact
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $integrationId,
        public readonly string $email,
        public readonly ContactType $type,
        public readonly string $firstName,
        public readonly string $lastName
    ) {
    }
}
