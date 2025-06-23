<?php

declare(strict_types=1);

return [
    'automatic_permissions_enabled' => env('UITPAS_AUTOMATIC_PERMISSIONS_ENABLED', false),
    'client_permissions_uri' => env('UITPAS_CLIENT_PERMISSIONS_URI'),
    'udb_base_uri' => env('UDB_BASE_URI'),
    'test' => [
        'organisation' => env('UITPAS_TEST_ORG', '0ce87cbc-9299-4528-8d35-92225dc9489f'),
        'api_endpoint' => env('UITPAS_TEST_ENDPOINT', ''),
        'oauth_token_url' => env('UITPAS_TEST_OAUTH_TOKEN_URL', ''),
        'client_id' => env('UITPAS_TEST_CLIENT_ID', ''),
        'client_secret' => env('UITPAS_TEST_CLIENT_SECRET', ''),
    ],
    'prod' => [
        'api_endpoint' => env('UITPAS_PROD_ENDPOINT', ''),
        'oauth_token_url' => env('UITPAS_PROD_OAUTH_TOKEN_URL', ''),
        'client_id' => env('UITPAS_PROD_CLIENT_ID', ''),
        'client_secret' => env('UITPAS_PROD_CLIENT_SECRET', ''),
    ],
];
