<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use Ramsey\Uuid\UuidInterface;

final readonly class DefaultScopeConfig implements ScopeConfig
{
    public function __construct(
        public UuidInterface $searchApiScopeId,
        public UuidInterface $entryApiScopeId,
        public UuidInterface $uitpasScopeId,
    ) {
    }

    /** @return UuidInterface[] */
    public function getScopeIdsFromIntegrationType(Integration $integration): array
    {
        return match ($integration->type) {
            IntegrationType::Widgets, IntegrationType::SearchApi => [$this->searchApiScopeId],
            IntegrationType::EntryApi => [$this->entryApiScopeId, $this->searchApiScopeId],
            IntegrationType::UiTPAS => [$this->uitpasScopeId, $this->entryApiScopeId, $this->searchApiScopeId]
        };
    }

    /** @return UuidInterface[] */
    public function getAll(): array
    {
        return [
            $this->searchApiScopeId,
            $this->entryApiScopeId,
            $this->uitpasScopeId,
        ];
    }
}
