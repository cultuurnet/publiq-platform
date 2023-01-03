<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum ProjectStage: string
{
    case TEST = 'test'; // Kick-off gesprek
    case START = 'start'; // Start bouw (ontwikkeling)
    case EVALUATION = 'evaluation'; // Evaluatie/kwaliteitscontrole
    case LIVE = 'live'; // Live en recrring
    case ENDED = 'ended'; // Contract beëindigd
}
