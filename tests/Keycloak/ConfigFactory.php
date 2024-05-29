<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Config;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;

trait ConfigFactory
{
    public function givenKeycloakConfig(Realm $realm): Config
    {
        return new Config(
            true,
            new RealmCollection([$realm]),
        );
    }
}
