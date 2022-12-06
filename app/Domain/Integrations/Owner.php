<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use Ramsey\Uuid\UuidInterface;

final class Owner
{
    public function __construct(
        public readonly OwnerId $ownerId,
        public readonly UuidInterface $integrationId,
        public readonly OwnerType $ownerType
    ) {
    }
}
