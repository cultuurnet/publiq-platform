<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Config;
use App\Keycloak\RealmCollection;
use Illuminate\Support\Facades\App;

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

    public function configureKeycloakConfigFacade(): void
    {
        // This is needed because some static calls in the Nova Resources use the Config object, and we don't want to hardcode the actual config values
        // Open to more elegant solutions.
        App::singleton(Config::class, function () {
            return $this->givenKeycloakConfig();
        });
    }
}
