<?php

declare(strict_types=1);

namespace App\ProjectAanvraag\Requests;

use App\Domain\Integrations\IntegrationStatus;
use Ramsey\Uuid\UuidInterface;

final readonly class CreateWidgetRequest
{
    public function __construct(
        public UuidInterface $integrationId,
        public string $userId,
        public string $name,
        public string $summary,
        public IntegrationStatus $status,
        public int $groupId,
        public string $testApiKeySapi3,
        public string $liveApiKeySapi3,
        public string $state
    ) {
    }
}
