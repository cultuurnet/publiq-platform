<?php

declare(strict_types=1);

namespace App\Keycloak\ClientId;

final class ClientIdFreeStringStrategy implements ClientIdFactory
{
    public function __construct(private readonly string $string)
    {
    }

    public function create(): string
    {
        return $this->string;
    }
}
