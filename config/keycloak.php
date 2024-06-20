<?php

declare(strict_types=1);

return [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'certificate' => __DIR__ . '/../publiq-keycloak.pem',
    'login' => [
        'internalName' => env('KEYCLOAK_LOGIN_REALM_NAME', ''),
        'base_url' => env('KEYCLOAK_LOGIN_BASE_URL', ''),
        'client_id' => env('KEYCLOAK_LOGIN_CLIENT_ID', ''),
        'client_secret' => env('KEYCLOAK_LOGIN_SECRET', ''),
        'redirect_uri' => env('KEYCLOAK_LOGIN_REDIRECT_URI', ''),
    ],
    'environments' => [
        'acc' => [
            'internalName' => env('KEYCLOAK_ACC_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_ACC_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_ACC_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_ACC_CLIENT_SECRET', ''),
            'scope' => [
                'search_api_id' => env('KEYCLOAK_ACC_SCOPE_SEARCH_API_ID', ''),
                'entry_api_id' => env('KEYCLOAK_ACC_SCOPE_ENTRY_API_ID', ''),
                'widgets_id' => env('KEYCLOAK_ACC_SCOPE_WIDGETS_ID', ''),
                'uitpas_id' => env('KEYCLOAK_ACC_SCOPE_UITPAS_ID', ''),
            ],
        ],
        'test' => [
            'internalName' => env('KEYCLOAK_TEST_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_TEST_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_TEST_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_TEST_CLIENT_SECRET', ''),
            'scope' => [
                'search_api_id' => env('KEYCLOAK_TEST_SCOPE_SEARCH_API_ID', ''),
                'entry_api_id' => env('KEYCLOAK_TEST_SCOPE_ENTRY_API_ID', ''),
                'widgets_id' => env('KEYCLOAK_TEST_SCOPE_WIDGETS_ID', ''),
                'uitpas_id' => env('KEYCLOAK_TEST_SCOPE_UITPAS_ID', ''),
            ],
        ],
        'prod' => [
            'internalName' => env('KEYCLOAK_PROD_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_PROD_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_PROD_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_PROD_CLIENT_SECRET', ''),
            'scope' => [
                'search_api_id' => env('KEYCLOAK_PROD_SCOPE_SEARCH_API_ID', ''),
                'entry_api_id' => env('KEYCLOAK_PROD_SCOPE_ENTRY_API_ID', ''),
                'widgets_id' => env('KEYCLOAK_PROD_SCOPE_WIDGETS_ID', ''),
                'uitpas_id' => env('KEYCLOAK_PROD_SCOPE_UITPAS_ID', ''),
            ],
        ],
    ],
];
