<?php

declare(strict_types=1);

namespace App\UiTPAS;

enum UiTPASPermissions: string
{
    case TARIFFS_READ = 'TARIFFS_READ';
    case TICKETSALES_REGISTER = 'TICKETSALES_REGISTER';
    case PASSES_READ = 'PASSES_READ';
    case PASSES_INSZNUMBERS_READ = 'PASSES_INSZNUMBERS_READ';
    case PASSES_CHIPNUMBERS_READ = 'PASSES_CHIPNUMBERS_READ';
}
