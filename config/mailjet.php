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
        'integration_activation_reminder' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REMINDER'),
    ],
    'mails' => [
        'integration_created' => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_CREATED'),
            'enabled' => true,
            'subject' => 'Welcome to Publiq platform - Let\'s get you started!',
        ],
        'integration_blocked' => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_BLOCKED'),
            'enabled' => false,
            'subject' => 'Publiq platform - Integration blocked',
        ],
        'integration_activated' => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATED'),
            'enabled' => true,
            'subject' => 'Publiq platform - Integration activated',
        ],
        'integration_activation_reminder' => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REMINDER'),
            'enabled' => true,
            'subject' => 'Publiq platform - Can we help you to activate your integration?',
        ],
        'integration_activation_requested' => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REQUESTED'),
            'enabled' => true,
            'subject' => 'Publiq platform - Integration activation requested',
        ],
    ],
];
