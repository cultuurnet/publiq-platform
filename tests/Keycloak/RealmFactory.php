<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\Realm;

trait RealmFactory
{
    public function givenAcceptanceRealm(): Realm
    {
        return new Realm(
            'uitidpoc',
            'Acceptange',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Acceptance,
        );
    }

    public function givenTestRealm(): Realm
    {
        return new Realm(
            'mytestrealm',
            'Test',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Testing,
        );
    }
}
