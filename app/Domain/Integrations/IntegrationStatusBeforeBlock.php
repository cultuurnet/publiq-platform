<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use Ramsey\Uuid\UuidInterface;

final class IntegrationStatusBeforeBlock
{
    public function __construct(
        public readonly UuidInterface $integrationId,
        public readonly IntegrationStatus $status
    ) {
    }
}
