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
        IntegrationType::EntryApi->value => (int)env('MAILJET_EXPIRATION_TIMER_ENTRY_API', 6),
        IntegrationType::SearchApi->value => (int)env('MAILJET_EXPIRATION_TIMER_SEARCH_API', 6),
        IntegrationType::UiTPAS->value => (int)env('MAILJET_EXPIRATION_TIMER_UITPAS', 6),
        IntegrationType::Widgets->value => (int)env('MAILJET_EXPIRATION_TIMER_WIDGETS', 3),
    ],
    'expiration_timers_final_reminder' => [ // Timer is always in months
        IntegrationType::EntryApi->value => (int)env('MAILJET_FINAL_EXPIRATION_TIMER_ENTRY_API', 12),
        IntegrationType::SearchApi->value => (int)env('MAILJET_FINAL_EXPIRATION_TIMER_SEARCH_API', 12),
        IntegrationType::UiTPAS->value => (int)env('MAILJET_FINAL_EXPIRATION_TIMER_UITPAS', 12),
        IntegrationType::Widgets->value => (int)env('MAILJET_FINAL_EXPIRATION_TIMER_WIDGETS', 12),
    ],
    'templates' => [
        TemplateName::INTEGRATION_CREATED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_CREATED'),
            'enabled' => true,
        ],
        TemplateName::INTEGRATION_ACTIVATED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATED'),
            'enabled' => true,
        ],
        TemplateName::INTEGRATION_ACTIVATION_REMINDER->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REMINDER'),
            'enabled' => true,
        ],
        TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_FINAL_ACTIVATION_REMINDER'),
            'enabled' => true,
        ],
        TemplateName::INTEGRATION_ACTIVATION_REQUEST->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_ACTIVATION_REQUEST'),
            'enabled' => true,
        ],
        TemplateName::INTEGRATION_DELETED->value => [
            'id' => env('MAILJET_TEMPLATE_INTEGRATION_DELETED'),
            'enabled' => true,
        ],
    ],
];
