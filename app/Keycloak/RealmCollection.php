<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Realm>
 */
final class RealmCollection extends Collection
{
    public static function build(): RealmCollection
    {
        $realms = new RealmCollection();

        foreach (config('keycloak.environments') as $publicName => $environment) {
            if (empty($environment['internalName']) || empty($environment['base_url']) || empty($environment['client_id']) || empty($environment['client_secret'])) {
                // If any of the fields are missing, do not create that realm.
                continue;
            }

            $realms->add(new Realm(
                $environment['internalName'],
                ucfirst($publicName),
                $environment['base_url'],
                $environment['client_id'],
                $environment['client_secret'],
                Environment::from($publicName)
            ));
        }

        return $realms;
    }

    public function fromPublicName(string $publicName): Realm
    {
        foreach ($this->all() as $realm) {
            if ($realm->publicName === $publicName) {
                return $realm;
            }
        }

        throw new InvalidArgumentException('Invalid realm: ' . $publicName);
    }

    /**
     * @return array<string, string>
     */
    public function asArray(): array
    {
        $output = [];
        foreach ($this->all() as $realm) {
            $output[$realm->publicName] = $realm->publicName;
        }
        return $output;
    }
}
