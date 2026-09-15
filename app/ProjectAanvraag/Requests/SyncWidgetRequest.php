<?php

declare(strict_types=1);

namespace App\ProjectAanvraag\Requests;

use App\Domain\Integrations\IntegrationStatus;
use Ramsey\Uuid\UuidInterface;

final readonly class SyncWidgetRequest
{
    public function __construct(
        public UuidInterface $integrationId,
        public string $userId,
        public string $name,
        public string $summary,
        public IntegrationStatus $status,
        public int $groupId,
        // Null for integrations without UiTiD v1 consumers, i.e. everything created after UiTiD v1 consumer
        // creation was switched off.
        public ?string $testApiKeySapi3,
        public ?string $liveApiKeySapi3,
        public string $testClientId,
        public string $liveClientId,
    ) {
    }
}
