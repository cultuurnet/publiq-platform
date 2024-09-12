<?php

declare(strict_types=1);


return [
    'enabled' => env('MAILJET_TRANSACTIONAL_EMAILS_ENABLED', false),
    'sandbox_mode' => env('MAILJET_SANDBOX_MODE', true),
    'api' => [
        'key' => env('MAILJET_API_KEY'),
        'secret' => env('MAILJET_API_SECRET'),
    ],
    'templates' => [
        'integration_created' => env('MAILJET_TEMPLATE_INTEGRATION_CREATED'),
        'integration_blocked' => env('MAILJET_TEMPLATE_INTEGRATION_BLOCKED'),
        'integration_activated' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATED'),
    ],
];
