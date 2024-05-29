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
