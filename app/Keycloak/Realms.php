<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use Illuminate\Support\Collection;

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
                Environment::from($publicName)
            ));
        }

        return $realms;
    }
}
