<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\Realm;
use App\Keycloak\Realms;
use App\Keycloak\ScopeConfig;
use Ramsey\Uuid\Uuid;

trait RealmFactory
{
    protected const SEARCH_SCOPE_ID = '06059529-74b5-422a-a499-ffcaf065d437';
    protected const ENTRY_SCOPE_ID = 'd8a54568-26da-412b-a441-d5e2fad84478';
    protected const UITPAS_SCOPE_ID = '0743b1c7-0ea2-46af-906e-fbb6c0317514';

    public function givenAllRealms(): Realms
    {
        return new Realms([
            $this->givenAcceptanceRealm(),
            $this->givenTestRealm(),
            $this->givenProductionRealm(),
        ]);
    }

    public function givenAcceptanceRealm(): Realm
    {
        return new Realm(
            'myAcceptanceRealm',
            'Acc',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Acceptance,
            $this->getScopeConfig(),
        );
    }

    public function givenTestRealm(): Realm
    {
        return new Realm(
            'myTestRealm',
            'Test',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Testing,
            $this->getScopeConfig(),
        );
    }

    public function givenProductionRealm(): Realm
    {
        return new Realm(
            'myProductRealm',
            'Prod',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Production,
            $this->getScopeConfig(),
        );
    }

    private function getScopeConfig(): ScopeConfig
    {
        return new ScopeConfig(
            Uuid::fromString(self::SEARCH_SCOPE_ID),
            Uuid::fromString(self::ENTRY_SCOPE_ID),
            Uuid::fromString(self::UITPAS_SCOPE_ID),
        );
    }
}
