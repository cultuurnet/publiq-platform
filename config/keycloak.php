<?php

declare(strict_types=1);

return [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'environments' => [
        'acceptance' => [
            'internalName' => env('KEYCLOAK_STAG_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_STAG_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_STAG_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_STAG_CLIENT_SECRET', ''),
        ],
        'testing' => [
            'internalName' => env('KEYCLOAK_TEST_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_TEST_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_TEST_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_TEST_CLIENT_SECRET', ''),
        ],
        'production' => [
            'internalName' => env('KEYCLOAK_PROD_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_PROD_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_PROD_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_PROD_CLIENT_SECRET', ''),
        ],
    ],
    'scope' => [
        'search_api_id' => env('KEYCLOAK_SCOPE_SEARCH_API_ID', ''),
        'entry_api_id' => env('KEYCLOAK_SCOPE_ENTRY_API_ID', ''),
        'widgets_id' => env('KEYCLOAK_SCOPE_WIDGETS_ID', ''),
        'uitpas_id' => env('KEYCLOAK_SCOPE_UITPAS_ID', ''),
    ],
];
