<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades;

use App\Domain\Integrations\KeyVisibility;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

final readonly class KeyVisibilityUpgrade
{
    private DateTimeImmutable $createdAt;

    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public KeyVisibility $keyVisibility
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
