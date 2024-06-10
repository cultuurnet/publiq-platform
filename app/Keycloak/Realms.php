<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * @extends Collection<int, Realm>
 */
final class Realms extends Collection
{
    public static function build(): self
    {
        $realms = new self();

        foreach (config('keycloak.environments') as $publicName => $environment) {
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
                    Uuid::fromString($environment['scope']['widgets_id']),
                    Uuid::fromString($environment['scope']['uitpas_id'])
                )
            ));
        }

        return $realms;
    }
}
