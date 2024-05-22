<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;

final readonly class Realm
{
    public function __construct(public string $internalName, public string $publicName, public Environment $environment = Environment::Unknown)
    {
    }

    public static function getMasterRealm(): self
    {
        return new self('master', 'Master');
    }
}
