<?php

declare(strict_types=1);

namespace App\Uitpas;

use App\Keycloak\Realm;

interface UitpasApiInterface
{
    public function addPermissions(Realm $realm, string $organizerId, string $clientId): void;
}
