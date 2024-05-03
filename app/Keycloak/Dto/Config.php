<?php

declare(strict_types=1);

namespace App\Keycloak\Dto;

use App\Keycloak\Collection\RealmCollection;

final class Config
{
    private RealmCollection $realms;

    public function __construct(
        private readonly bool $enabled, // This is a feature flag
        private string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        Realm ...$realms
    ) {
        $this->baseUrl = $this->addTrailingSlash($baseUrl);
        $this->realms = new RealmCollection($realms);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getRealms(): RealmCollection
    {
        return $this->realms;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    private function addTrailingSlash(string $uri): string
    {
        if (str_ends_with($uri, '/')) {
            return $uri;
        }

        return $uri . '/';
    }
}
