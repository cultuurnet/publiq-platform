<?php

declare(strict_types=1);

namespace App\Mails\Template;

enum TemplateName: string
{
    case INTEGRATION_CREATED = 'created';
    case INTEGRATION_ACTIVATION_REMINDER = 'activation_reminder';
    case INTEGRATION_FINAL_ACTIVATION_REMINDER = 'final_activation_reminder';
    case INTEGRATION_ACTIVATED = 'activated';
    case INTEGRATION_ACTIVATION_REQUEST = 'requested';
    case INTEGRATION_DELETED = 'deleted';

    // These approvals/rejection below are different from general integration ones because they are on the level of the UdbOrganizer
    case ORGANISATION_UITPAS_REQUESTED = 'uitpas.requested';
    case ORGANISATION_UITPAS_APPROVED = 'uitpas.approved';
    case ORGANISATION_UITPAS_REJECTED = 'uitpas.rejected';
}
