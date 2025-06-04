<?php

declare(strict_types=1);

return [
    'automatic_permissions_enabled' => env('UITPAS_AUTOMATIC_PERMISSIONS_ENABLED', false),
    'test' => [
        'organisation' => env('UITPAS_TEST_ORG', '0ce87cbc-9299-4528-8d35-92225dc9489f'),
        'api_endpoint' => env('UITPAS_TEST_ENDPOINT', 'https://api-test.uitpas.be/'),
    ],
    'prod' => [
        'api_endpoint' => env('UITPAS_PROD_ENDPOINT', 'https://api.uitpas.be/'),
    ],
];
