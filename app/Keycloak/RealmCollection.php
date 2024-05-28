<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @extends Collection<int, Realm>
 */
final class RealmCollection extends Collection
{
    public static function getRealms(): RealmCollection
    {
        //@todo Change this once all Realms have been configured
        return new self([new Realm('uitidpoc', 'Acceptance', Environment::Acceptance)]);
    }

    public static function fromPublicName(string $publicName): Realm
    {
        foreach (self::getRealms() as $realm) {
            if ($realm->publicName === $publicName) {
                return $realm;
            }
        }

        throw new InvalidArgumentException('Invalid realm: ' . $publicName);
    }

    /**
     * @return array<string, string>
     */
    public static function asArray(): array
    {
        $output = [];
        foreach (self::getRealms() as $realm) {
            $output[$realm->publicName] = $realm->publicName;
        }
        return $output;
    }
}
