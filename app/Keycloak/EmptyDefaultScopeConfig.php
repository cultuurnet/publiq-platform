<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Integration;
use Ramsey\Uuid\UuidInterface;

final readonly class EmptyDefaultScopeConfig implements ScopeConfig
{
    public function __construct(
    ) {
    }

    /** @return UuidInterface[] */
    public function getScopeIdsFromIntegrationType(Integration $integration): array
    {
        return [];
    }

    /** @return UuidInterface[] */
    public function getAll(): array
    {
        return [];
    }
}
