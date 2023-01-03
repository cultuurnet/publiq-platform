<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum ProjectStage: string
{
    case TEST = 'test';
    case START = 'start';
    case EVALUATION = 'evaluation';
    case LIVE = 'live';
    case ENDED = 'ended';
}
