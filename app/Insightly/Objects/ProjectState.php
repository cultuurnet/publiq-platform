<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum ProjectState: string
{
    case NOT_STARTED = 'Not Started';
    case IN_PROGRESS = 'In Progress';
    case DEFERRED = 'Deferred';
    case CANCELLED = 'Cancelled';
    case ABANDONED = 'Abandoned';
    case COMPLETED = 'Completed';
}
