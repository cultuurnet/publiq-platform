<?php

declare(strict_types=1);

namespace App\Keycloak\TokenStrategy;

use App\Keycloak\Client\KeycloakHttpClient;
use App\Keycloak\Realm;

interface TokenStrategy
{
    public function fetchToken(KeycloakHttpClient $client, Realm $realm): string;
}
