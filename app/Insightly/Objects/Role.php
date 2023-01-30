<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

enum Role: string
{
    case Technical = 'Technisch';
    case Applicant = 'Aanvrager';
}
