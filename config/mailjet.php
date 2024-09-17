<?php

declare(strict_types=1);


use App\Mails\Template\Templates;

return [
    'enabled' => env('MAILJET_TRANSACTIONAL_EMAILS_ENABLED', false),
    'sandbox_mode' => env('MAILJET_SANDBOX_MODE', true),
    'api' => [
        'key' => env('MAILJET_API_KEY'),
        'secret' => env('MAILJET_API_SECRET'),
    ],
    'mails' => [
        Templates::INTEGRATION_CREATED => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_CREATED'),
            'enabled' => true,
            'subject' => 'Welcome to Publiq platform - Let\'s get you started!',
        ],
        Templates::INTEGRATION_BLOCKED => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_BLOCKED'),
            'enabled' => true,
            'subject' => 'Publiq platform - Integration blocked',
        ],
        Templates::INTEGRATION_ACTIVATED => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATED'),
            'enabled' => true,
            'subject' => 'Publiq platform - Integration activated',
        ],
        Templates::INTEGRATION_ACTIVATION_REMINDER => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REMINDER'),
            'enabled' => true,
            'subject' => 'Publiq platform - Can we help you to activate your integration?',
        ],
    ],
];
