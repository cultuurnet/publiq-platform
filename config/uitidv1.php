<?php

declare(strict_types=1);

return [
    'enabled' => env('UITID_V1_CONSUMER_CREATION_ENABLED', true),

    // UiTiD v1 environments configuration, used to store/update consumers in UiTiD v1.
    // Note that local/staging/acceptance/testing environments of publiq platform should actually use the ACC
    // environment of UiTiD v1 as replacements for the test/prod environments of UiTiD v1. Otherwise, they will create
    // real clients on the test/prod environments which we do not want.
    'environments' => [
        'acc' => [
            'baseUrl' => env('UITID_V1_ACC_URL'),
            'consumerKey' => env('UITID_V1_ACC_CONSUMER_KEY'),
            'consumerSecret' => env('UITID_V1_ACC_CONSUMER_SECRET'),
            'groups' => [
                'entry-api' => env('UITID_V1_ACC_GROUPS_ENTRY_API'),
                'search-api' => env('UITID_V1_ACC_GROUPS_SEARCH_API'),
                'widgets' => env('UITID_V1_ACC_GROUPS_WIDGETS'),
                'uitpas' => env('UITID_V1_ACC_GROUPS_UITPAS'),
            ],
            'consumerDetailUrlTemplate' => env('UITID_V1_ACC_CONSUMER_DETAILS_URL_TEMPLATE'),
        ],
        'test' => [
            'baseUrl' => env('UITID_V1_TEST_URL'),
            'consumerKey' => env('UITID_V1_TEST_CONSUMER_KEY'),
            'consumerSecret' => env('UITID_V1_TEST_CONSUMER_SECRET'),
            'groups' => [
                'entry-api' => env('UITID_V1_TEST_GROUPS_ENTRY_API'),
                'search-api' => env('UITID_V1_TEST_GROUPS_SEARCH_API'),
                'widgets' => env('UITID_V1_TEST_GROUPS_WIDGETS'),
                'uitpas' => env('UITID_V1_TEST_GROUPS_UITPAS'),
            ],
            'consumerDetailUrlTemplate' => env('UITID_V1_TEST_CONSUMER_DETAILS_URL_TEMPLATE'),
        ],
        'prod' => [
            'baseUrl' => env('UITID_V1_PROD_URL'),
            'consumerKey' => env('UITID_V1_PROD_CONSUMER_KEY'),
            'consumerSecret' => env('UITID_V1_PROD_CONSUMER_SECRET'),
            'groups' => [
                'entry-api' => env('UITID_V1_PROD_GROUPS_ENTRY_API'),
                'search-api' => env('UITID_V1_PROD_GROUPS_SEARCH_API'),
                'widgets' => env('UITID_V1_PROD_GROUPS_WIDGETS'),
                'uitpas' => env('UITID_V1_PROD_GROUPS_UITPAS'),
            ],
            'consumerDetailUrlTemplate' => env('UITID_V1_PROD_CONSUMER_DETAILS_URL_TEMPLATE'),
        ],
    ],
];
