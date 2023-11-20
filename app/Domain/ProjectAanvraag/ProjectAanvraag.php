<?php

namespace App\Domain\ProjectAanvraag;

use Ramsey\Uuid\UuidInterface;

final class ProjectAanvraag
{
    public function __construct(
        public readonly UuidInterface $integrationId,
        public readonly int $projectAanvraagId,
    ) {
    }
}
