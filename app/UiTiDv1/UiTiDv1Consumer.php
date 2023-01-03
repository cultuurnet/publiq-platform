<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use Ramsey\Uuid\UuidInterface;

final class UiTiDv1Consumer
{
    public function __construct(
        public readonly UuidInterface $integrationId,
        public readonly string $consumerKey,
        public readonly string $consumerSecret,
        public readonly string $apiKey,
        public readonly UiTiDv1Environment $environment
    ) {
    }
}
