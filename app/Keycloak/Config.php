<?php

declare(strict_types=1);

namespace App\Keycloak;

final readonly class Config
{
    public string $baseUrl;

    public function __construct(
        public bool $isEnabled, // This is a feature flag
        string $baseUrl,
        public string $clientId,
        public string $clientSecret,
        public RealmCollection $realms
    ) {
        $this->baseUrl = $this->addTrailingSlash($baseUrl);
    }

    private function addTrailingSlash(string $uri): string
    {
        if (str_ends_with($uri, '/')) {
            return $uri;
        }

        return $uri . '/';
    }
}
