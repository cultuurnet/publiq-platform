<?php

declare(strict_types=1);

return [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'base_url' => env('KEYCLOAK_BASE_URL', ''),
    'client_id' => env('KEYCLOAK_CLIENT_ID', ''),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', ''),
];
