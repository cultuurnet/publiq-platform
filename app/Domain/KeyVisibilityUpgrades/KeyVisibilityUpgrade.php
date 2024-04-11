<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades;

use App\Domain\Integrations\KeyVisibility;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

final class KeyVisibilityUpgrade
{
    private ?DateTimeImmutable $createdAt;

    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $integrationId,
        public readonly KeyVisibility $keyVisibility
    ) {
        $this->createdAt = null;
    }

    public function withCreatedAt(DateTimeImmutable $createdAt): self
    {
        $clone = clone $this;
        $clone->createdAt = $createdAt;
        return $clone;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function getDaysLeft(): int
    {
        return $this->createdAt->diff(new DateTimeImmutable())->days;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'integrationId' => $this->integrationId,
            'keyVisibility' => $this->keyVisibility,
            'daysLeft' => $this->getDaysLeft(),
        ];
    }
}
