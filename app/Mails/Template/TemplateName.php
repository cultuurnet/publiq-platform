<?php

declare(strict_types=1);

namespace App\Mails\Template;

enum TemplateName: string
{
    case INTEGRATION_CREATED = 'integration.created';
    case INTEGRATION_ACTIVATION_REMINDER = 'integration.activation_reminder';
    case INTEGRATION_FINAL_ACTIVATION_REMINDER = 'integration.final_activation_reminder';
    case INTEGRATION_ACTIVATED = 'integration.activated';
    case INTEGRATION_ACTIVATION_REQUEST = 'integration.requested';
    case INTEGRATION_DELETED = 'integration.deleted';

    // These approvals/rejection below are different from general integration ones because they are on the level of the UdbOrganizer
    case ORGANISATION_UITPAS_REQUESTED = 'integration.uitpas.requested';
    case ORGANISATION_UITPAS_APPROVED = 'integration.uitpas.approved';
    case ORGANISATION_UITPAS_REJECTED = 'integration.uitpas.rejected';
}
