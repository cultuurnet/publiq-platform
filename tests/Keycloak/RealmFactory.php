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
            'Acceptance',
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
            'Testing',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Testing,
        );
    }
}
