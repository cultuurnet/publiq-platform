<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum UdbOrganizerStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
}
