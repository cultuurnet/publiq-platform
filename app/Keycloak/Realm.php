<?php

declare(strict_types=1);

namespace App\Keycloak\Dto;

final readonly class Realm
{
    public function __construct(public string $internalName, public string $publicName)
    {
    }

    public static function getMasterRealm(): self
    {
        return new self('master', 'Master');
    }
}
