<?php

declare(strict_types=1);

namespace App\Keycloak;

final readonly class Config
{
    public function __construct(
        public bool $isEnabled, // This is a feature flag
        public RealmCollection $realms
    ) {
    }
}
