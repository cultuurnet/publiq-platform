<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum OpportunityStage: string
{
    case TEST = 'test'; // Toegang tot test
    case REQUEST = 'request'; // Aanvraag
    case INFORMATION = 'information'; // Informatie/toelichting
    case OFFER = 'offer'; // Voorstel
    case CLOSED = 'closed'; // Closed
}
