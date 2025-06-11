<?php

declare(strict_types=1);

return [
    'automatic_permissions_enabled' => env('UITPAS_AUTOMATIC_PERMISSIONS_ENABLED', false),
    'test' => [
        'organisation' => env('UITPAS_TEST_ORG', '0ce87cbc-9299-4528-8d35-92225dc9489f'),
        'api_endpoint' => env('UITPAS_TEST_ENDPOINT', ''),
        'oath_token_url' => env('UITPAS_TEST_OATH_TOKEN_URL', ''),
        'client_id' => env('UITPAS_TEST_CLIENT_ID', ''),
        'client_secret' => env('UITPAS_TEST_CLIENT_SECRET', ''),
    ],
    'prod' => [
        'api_endpoint' => env('UITPAS_PROD_ENDPOINT', ''),
        'oath_token_url' => env('UITPAS_PROD_OATH_TOKEN_URL', ''),
        'client_id' => env('UITPAS_PROD_CLIENT_ID', ''),
        'client_secret' => env('UITPAS_PROD_CLIENT_SECRET', ''),
    ],
];
