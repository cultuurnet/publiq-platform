<?php

declare(strict_types=1);

namespace App\Keycloak\Dto;

final class Realm
{
    public function __construct(private string $internalName, private string $publicName)
    {
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function getPublicName(): string
    {
        return $this->publicName;
    }

    public static function getMasterRealm(): self
    {
        return new self('master', 'Master');
    }
}
