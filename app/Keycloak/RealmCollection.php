<?php

declare(strict_types=1);

namespace App\Keycloak;

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
        return new self([new Realm('uitidpoc', 'Acceptance')]);
    }

    public static function fromInternalName(string $internalName) : Realm
    {
        foreach(self::getRealms() as $realm) {
            if($realm->internalName === $internalName) {
                return $realm;
            }
        }

        throw new InvalidArgumentException('Invalid realm: '. $internalName);
    }
}
