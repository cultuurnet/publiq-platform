<?php

declare(strict_types=1);

namespace App\Keycloak\ClientId;

use Ramsey\Uuid\Uuid;

final class ClientIdUuidStrategy implements ClientIdFactory
{
    public function create(): string
    {
        return Uuid::uuid4()->toString();
    }
}
