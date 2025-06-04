<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Integration;
use Ramsey\Uuid\UuidInterface;

interface ScopeConfig
{
    /** @return UuidInterface[] */
    public function getScopeIdsFromIntegrationType(Integration $integration): array;

    /** @return UuidInterface[] */
    public function getAll(): array;
}
