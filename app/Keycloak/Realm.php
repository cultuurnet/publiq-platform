<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use App\UiTPAS\UiTiDRealms;

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

    private function addTrailingSlash(string $uri): string
    {
        if (str_ends_with($uri, '/')) {
            return $uri;
        }

        return $uri . '/';
    }

    public static function getUitIdTestRealm(): Realm
    {
        return new Realm(
            config(UiTiDRealms::TEST_INTERNAL_NAME->value),
            config(UiTiDRealms::TEST_INTERNAL_NAME->value),
            config(UiTiDRealms::TEST_BASE_URL->value),
            config(UiTiDRealms::TEST_CLIENT_ID->value),
            config(UiTiDRealms::TEST_CLIENT_SECRET->value),
            Environment::Testing,
            new EmptyDefaultScopeConfig()
        );
    }

    public static function getUitIdProdRealm(): Realm
    {
        return new Realm(
            config(UiTiDRealms::PROD_INTERNAL_NAME->value),
            config(UiTiDRealms::PROD_INTERNAL_NAME->value),
            config(UiTiDRealms::PROD_BASE_URL->value),
            config(UiTiDRealms::PROD_CLIENT_ID->value),
            config(UiTiDRealms::PROD_CLIENT_SECRET->value),
            Environment::Production,
            new EmptyDefaultScopeConfig()
        );
    }
}
