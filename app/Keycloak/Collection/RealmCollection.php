<?php

declare(strict_types=1);

namespace App\Keycloak\Collection;

use App\Keycloak\Dto\Realm;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Realm>
 */
final class RealmCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Realm::class;
    }

    public static function getDefaultRealms(): self
    {
        //@todo This will change later when we a acc / test / prod env
        return new self([new Realm('uitidpoc', 'Acceptance')]);
    }
}
