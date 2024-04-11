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
        if ($this->createdAt === null) {
            throw new \Exception('Property createdAt should exist');
        }

        $days = $this->createdAt->diff(new DateTimeImmutable())->days;
        // days can return false if the DateInterval is not created by the diff method
        assert(is_int($days), 'days should be an integer');

        return $days;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'integrationId' => $this->integrationId,
            'keyVisibility' => $this->keyVisibility,
            'daysLeft' => $this->getDaysLeft(),
        ];
    }
}
