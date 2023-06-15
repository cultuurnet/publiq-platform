<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use Ramsey\Uuid\UuidInterface;

final class IntegrationUrl
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly UuidInterface $integrationId,
        public readonly Environment $environment,
        public readonly IntegrationUrlType $type,
        public readonly string $url
    ) {
    }
}
