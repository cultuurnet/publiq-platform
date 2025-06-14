<?php

declare(strict_types=1);

use Auth0\SDK\Configuration\SdkConfiguration;

return [
    'loginEnabled' => env('KEYCLOAK_LOGIN_ENABLED', false),
    'creationEnabled' => env('KEYCLOAK_CREATION_ENABLED', false),
    'testClientEnabled' => env('KEYCLOAK_TEST_CLIENT_ENABLED', false),
    'login' => [
        'strategy' => SdkConfiguration::STRATEGY_REGULAR,
        'enforce2FAForAdmins' => env('KEYCLOAK_ENFORCE_2FA_FOR_ADMINS', false),
        'domain' => env('KEYCLOAK_LOGIN_DOMAIN'),
        'managementDomain' => env('KEYCLOAK_LOGIN_MANAGEMENT_DOMAIN'),
        'clientId' => env('KEYCLOAK_LOGIN_CLIENT_ID'),
        'clientSecret' => env('KEYCLOAK_LOGIN_CLIENT_SECRET'),
        'audience' => env('KEYCLOAK_LOGIN_AUDIENCE'),
        'realmName' => env('KEYCLOAK_LOGIN_REALM_NAME'),
        'parameters' => env('KEYCLOAK_LOGIN_PARAMETERS'),
        'cookieSecret' => env('KEYCLOAK_LOGIN_COOKIE_SECRET', env('APP_KEY')),
        'cookieExpires' => env('COOKIE_EXPIRES', 0),
        'redirectUri' => env('KEYCLOAK_LOGIN_REDIRECT_URI', env('APP_URL') . '/callback'),
    ],
    'environments' => [
        // These are master realm clients
        'acc' => [
            'internalName' => env('KEYCLOAK_ACC_REALM_NAME', ''),
            'base_url' => env('KEYCLOAK_ACC_BASE_URL', ''),
            'client_id' => env('KEYCLOAK_ACC_CLIENT_ID', ''),
            'client_secret' => env('KEYCLOAK_ACC_CLIENT_SECRET', ''),
            'scope' => [
                'search_api_id' => env('KEYCLOAK_ACC_SCOPE_SEARCH_API_ID', ''),
                'entry_api_id' => env('KEYCLOAK_ACC_SCOPE_ENTRY_API_ID', ''),
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
                'uitpas_id' => env('KEYCLOAK_PROD_SCOPE_UITPAS_ID', ''),
            ],
        ],
    ],
];
