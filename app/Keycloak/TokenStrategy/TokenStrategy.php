<?php

declare(strict_types=1);

namespace App\Keycloak\TokenStrategy;

use App\Keycloak\Client\HttpClient;
use App\Keycloak\Realm;

interface TokenStrategy
{
    public function fetchToken(HttpClient $client, Realm $realm): string;
}
