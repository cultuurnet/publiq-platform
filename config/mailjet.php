<?php

declare(strict_types=1);

use App\Domain\Integrations\IntegrationType;
use App\Mails\Template\TemplateName;

return [
    'enabled' => env('MAILJET_TRANSACTIONAL_EMAILS_ENABLED', false),
    'sandbox_mode' => env('MAILJET_SANDBOX_MODE', true),
    'sandbox_allowed_domains' => array_map(static fn ($value) => trim($value), explode(',', env('MAILJET_SANDBOX_ALLOWED_DOMAINS', ''))),
    'api' => [
        'key' => env('MAILJET_API_KEY'),
        'secret' => env('MAILJET_API_SECRET'),
    ],
    'expiration_timers' => [ // Timer is always in months
        IntegrationType::EntryApi->value => env('MAILJET_EXPIRATION_TIMER_ENTRY_API', 6),
        IntegrationType::SearchApi->value => env('MAILJET_EXPIRATION_TIMER_SEARCH_API', 6),
        IntegrationType::UiTPAS->value => env('MAILJET_EXPIRATION_TIMER_UITPAS', 6),
        IntegrationType::Widgets->value => env('MAILJET_EXPIRATION_TIMER_WIDGETS', 3),
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
        TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REQUEST'),
            'enabled' => true,
            'subject' => 'Publiq platform - Request for activating integration',
        ],
        TemplateName::INTEGRATION_DELETED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_DELETED'),
            'enabled' => true,
            'subject' => 'Publiq platform - Integration deleted',
        ],
    ],
];
