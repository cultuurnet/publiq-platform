<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\Realm;
use App\Keycloak\Realms;

trait RealmFactory
{
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
        );
    }

}
