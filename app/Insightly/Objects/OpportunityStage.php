<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum OpportunityStage: string
{
    case TEST = 'test';
    case REQUEST = 'request';
    case INFORMATION = 'information';
    case OFFER = 'offer';
    case CLOSED = 'closed';
}
