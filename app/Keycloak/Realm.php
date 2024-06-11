<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;

final readonly class Realm
{
    public string $baseUrl;

    public function __construct(
        public string $internalName,
        public string $publicName,
        string $baseUrl,
        public string $clientId,
        public string $clientSecret,
        public Environment $environment
    ) {
        $this->baseUrl = $this->addTrailingSlash($baseUrl);
    }

    public function getMasterRealm(): self
    {
        return new self(
            'master',
            'Master',
            $this->baseUrl,
            $this->clientId,
            $this->clientSecret,
            $this->environment
        );
    }

    private function addTrailingSlash(string $uri): string
    {
        if (str_ends_with($uri, '/')) {
            return $uri;
        }

        return $uri . '/';
    }
}
