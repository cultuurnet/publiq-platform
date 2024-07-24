<?php

declare(strict_types=1);

namespace App\Keycloak\ClientId;

interface ClientIdFactory
{
    public function create(): string;
}
