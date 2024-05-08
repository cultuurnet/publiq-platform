<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use Ramsey\Uuid\UuidInterface;

final class ScopeConfig
{
    public function __construct(
        public UuidInterface $searchApi,
        public UuidInterface $entryApi,
        public UuidInterface $widgets,
    ) {
    }

    public function getScopeIdFromIntegrationType(Integration $integration): UuidInterface
    {
        return match ($integration->type) {
            IntegrationType::EntryApi => $this->entryApi,
            IntegrationType::Widgets => $this->widgets,
            IntegrationType::SearchApi => $this->searchApi
        };
    }
}
