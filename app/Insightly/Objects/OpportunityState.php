<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum OpportunityState: string
{
    case ABANDONED = 'Abandoned';
    case LOST = 'Lost';
    case SUSPENDED = 'Suspended';
    case WON = 'Won';
    case OPEN = 'Open';
}
