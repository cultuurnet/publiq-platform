<?php

declare(strict_types=1);

namespace App\Keycloak\TokenStrategy;

use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\Realm;

interface TokenStrategy
{
    public function fetchToken(KeycloakGuzzleClient $client, Realm $realm): string;
}
