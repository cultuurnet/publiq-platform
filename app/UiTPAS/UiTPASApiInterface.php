<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Keycloak\Client;
use App\Keycloak\Realm;

interface UiTPASApiInterface
{
    public function addPermissions(Realm $realm, string $organizerId, string $clientId): void;

    /** @return string[] */
    public function fetchPermissions(Realm $realm, Client $keycloakClient, string $organizerId): array;
}
