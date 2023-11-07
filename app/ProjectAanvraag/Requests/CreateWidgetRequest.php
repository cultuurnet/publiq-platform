<?php

declare(strict_types=1);

namespace App\ProjectAanvraag\Requests;

use Ramsey\Uuid\UuidInterface;

final readonly class CreateWidgetRequest
{
    public function __construct(
        public UuidInterface $integrationId,
        public UuidInterface $userId,
        public string $name,
        public string $summary,
        public int $groupId,
        public string $testApiKeySapi3,
        public string $liveApiKeySapi3
    ) {
    }
}
