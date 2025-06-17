<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use Ramsey\Uuid\UuidInterface;

final readonly class UdbOrganizer
{
    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public string $organizerId,
        public UdbOrganizerStatus $status = UdbOrganizerStatus::Pending,
    ) {
    }

    public function withStatus(UdbOrganizerStatus $status): self
    {
        return new self(
            $this->id,
            $this->integrationId,
            $this->organizerId,
            $status
        );
    }
}
