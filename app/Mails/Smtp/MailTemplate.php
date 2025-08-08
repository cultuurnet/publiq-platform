<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

enum MailTemplate: int
{
    case INTEGRATION_CREATED = 0;
    case ORGANISATION_UITPAS_REQUESTED = 1;
    case INTEGRATION_ACTIVATED = 2;
    case ORGANISATION_UITPAS_APPROVED = 3;
    case ORGANISATION_UITPAS_REJECTED = 4;
}
