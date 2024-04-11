<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades;

use App\Domain\Integrations\KeyVisibility;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

final class KeyVisibilityUpgrade
{
    private DateTimeImmutable $createdAt;

    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $integrationId,
        public readonly KeyVisibility $keyVisibility
    ) {
    }

    public function withCreatedAt(DateTimeImmutable $createdAt): self
    {
        $clone = clone $this;
        $clone->createdAt = $createdAt;
        return $clone;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
