<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Api\ClientCredentialsContext;
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
        public Environment $environment,
        public ScopeConfig $scopeConfig,
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
            $this->environment,
            $this->scopeConfig,
        );
    }

    public function getContext(): ClientCredentialsContext
    {
        return new ClientCredentialsContext(
            $this->environment,
            $this->baseUrl,
            $this->clientId,
            $this->clientSecret,
            $this->internalName
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
