<?php

declare(strict_types=1);


use App\Mails\Template\TemplateName;

return [
    'enabled' => env('MAILJET_TRANSACTIONAL_EMAILS_ENABLED', false),
    'sandbox_mode' => env('MAILJET_SANDBOX_MODE', true),
    'api' => [
        'key' => env('MAILJET_API_KEY'),
        'secret' => env('MAILJET_API_SECRET'),
    ],
    'templates' => [
        TemplateName::INTEGRATION_CREATED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_CREATED'),
            'enabled' => true,
            'subject' => 'Welcome to Publiq platform - Let\'s get you started!',
        ],
        TemplateName::INTEGRATION_BLOCKED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_BLOCKED'),
            'enabled' => false,
            'subject' => 'Publiq platform - Integration blocked',
        ],
        TemplateName::INTEGRATION_ACTIVATED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATED'),
            'enabled' => true,
            'subject' => 'Publiq platform - Integration activated',
        ],
        TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REMINDER'),
            'enabled' => true,
            'subject' => 'Publiq platform - Can we help you to activate your integration?',
        ],
    ],
];
