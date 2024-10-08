<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\Exception\RealmNotAvailable;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * @extends Collection<int, Realm>
 */
final class Realms extends Collection
{
    public function __construct(array $realms = [])
    {
        parent::__construct($realms);
    }

    public static function build(): self
    {
        $realms = new self();

        foreach (config('keycloak.environments') as $publicName => $environment) {
            if ($environment['base_url'] === '') {
                continue;
            }

            $realms->add(new Realm(
                $environment['internalName'],
                ucfirst($publicName),
                $environment['base_url'],
                $environment['client_id'],
                $environment['client_secret'],
                Environment::from($publicName),
                new ScopeConfig(
                    Uuid::fromString($environment['scope']['search_api_id']),
                    Uuid::fromString($environment['scope']['entry_api_id']),
                    Uuid::fromString($environment['scope']['uitpas_id'])
                )
            ));
        }

        return $realms;
    }

    /**
     * @throws RealmNotAvailable
     */
    public function getRealmByEnvironment(Environment $environment): Realm
    {
        foreach ($this->items as $realm) {
            if ($realm->environment === $environment) {
                return $realm;
            }
        }

        throw RealmNotAvailable::realmNotAvailable($environment);
    }
}
