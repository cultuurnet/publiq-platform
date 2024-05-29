<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;

final class ConfigFactory
{
    public static function build(): Config
    {
        return new Config(
            config('keycloak.enabled'),
            self::buildRealmCollection()
        );
    }

    private static function buildRealmCollection(): RealmCollection
    {
        $realms = new RealmCollection();

        foreach (config('keycloak.environments') as $publicName => $environment) {
            if (empty($environment['internalName']) || empty($environment['base_url']) || empty($environment['client_id']) || empty($environment['client_secret'])) {
                // If any of the fields are missing, do not create that realm.
                continue;
            }

            $realms->add(new Realm(
                ucfirst($publicName),
                $environment['internalName'],
                $environment['base_url'],
                $environment['client_id'],
                $environment['client_secret'],
                Environment::fromString($publicName)
            ));
        }
        return $realms;
    }
}
