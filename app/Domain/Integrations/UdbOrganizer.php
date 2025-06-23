<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Udb3Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class UdbOrganizer
{
    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public Udb3Uuid $organizerId,
        public UdbOrganizerStatus $status,
    ) {
    }
}
