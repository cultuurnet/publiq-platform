<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

enum MailerTemplate: int
{
    case INTEGRATION_ACTIVATED = 0;
    case ORGANISATION_UITPAS_REQUESTED = 1;
    case ORGANISATION_UITPAS_APPROVED = 2;
    case ORGANISATION_UITPAS_REJECTED = 3;
}
