<?php

declare(strict_types=1);

namespace App\Keycloak\TokenStrategy;

use App\Keycloak\Realm;

interface TokenStrategy
{
    public function fetchToken(Realm $realm): string;
}