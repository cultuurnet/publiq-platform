<?php

declare(strict_types=1);

namespace App\Keycloak;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Realm>
 */
final class RealmCollection extends Collection
{
    public static function getDefaultRealms(): RealmCollection
    {
        //@todo Change this once all Realms have been configured
        return new self([new Realm('uitidpoc', 'Acceptance')]);
    }
}
