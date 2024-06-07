<?php

declare(strict_types=1);

namespace App\Keycloak;

enum KeycloakConfig: string
{
    case isEnabled = 'keycloak.enabled';
}
