<?php

declare(strict_types=1);

namespace App\Mails\Template;

enum TemplateName: string
{
    case INTEGRATION_CREATED = 'integration_created';
    case INTEGRATION_ACTIVATION_REMINDER = 'integration_activation_reminder';
    case INTEGRATION_FINAL_ACTIVATION_REMINDER = 'integration_final_activation_reminder';
    case INTEGRATION_ACTIVATED = 'integration_activated';
    case INTEGRATION_ACTIVATION_REQUEST = 'integration_activation_request';
    case INTEGRATION_DELETED = 'integration_deleted';
}
