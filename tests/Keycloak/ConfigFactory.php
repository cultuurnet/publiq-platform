<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Config;
use App\Keycloak\RealmCollection;

trait ConfigFactory
{
    use RealmFactory;

    public function givenKeycloakConfig(): Config
    {
        return new Config(
            true,
            new RealmCollection([$this->givenAcceptanceRealm(), $this->givenTestRealm()]),
        );
    }
}
