<?php

declare(strict_types=1);

return [
    'create_widget' => env('PROJECT_AANVRAAG_CREATE_WIDGET', false),
    'base_uri' => [
        'test' => env('PROJECT_AANVRAAG_BASE_URI_TEST', 'http://localhost/'),
        'live' => env('PROJECT_AANVRAAG_BASE_URI_LIVE', 'http://localhost/'),
    ],
    'connect_timeout' => env('PROJECT_AANVRAAG_CONNECT_TIMEOUT', 10.0)
];
