<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use Ramsey\Uuid\UuidInterface;

final readonly class ScopeConfig
{
    public function __construct(
        public UuidInterface $searchApiScopeId,
        public UuidInterface $entryApiScopeId,
        public UuidInterface $widgetScopeId,
        public UuidInterface $uitpasScopeId,
    ) {
    }

    public function getScopeIdFromIntegrationType(Integration $integration): UuidInterface
    {
        return match ($integration->type) {
            IntegrationType::EntryApi => $this->entryApiScopeId,
            IntegrationType::Widgets => $this->widgetScopeId,
            IntegrationType::SearchApi => $this->searchApiScopeId,
            IntegrationType::UiTPAS => $this->uitpasScopeId
        };
    }
}
