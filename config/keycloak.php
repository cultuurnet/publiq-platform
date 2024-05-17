<?php

declare(strict_types=1);

return [
    'enabled' => env('KEYCLOAK_ENABLE', false),
    'base_url' => env('KEYCLOAK_BASE_URL', ''),
    'client_id' => env('KEYCLOAK_CLIENT_ID', ''),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', ''),
    'scope' => [
        'search_api_id' => env('KEYCLOAK_SCOPE_SEARCH_API_ID', ''),
        'entry_api_id' => env('KEYCLOAK_SCOPE_ENTRY_API_ID', ''),
        'widgets_id' => env('KEYCLOAK_SCOPE_WIDGETS_ID', ''),
        'uitpas_id' => env('KEYCLOAK_SCOPE_UITPAS_ID', ''),
    ],
];
