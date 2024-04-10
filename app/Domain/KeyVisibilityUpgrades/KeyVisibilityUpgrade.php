<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades;

use App\Domain\Integrations\KeyVisibility;
use Ramsey\Uuid\UuidInterface;

final readonly class KeyVisibilityUpgrade
{
    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public KeyVisibility $keyVisibility
    ) {
    }
}
