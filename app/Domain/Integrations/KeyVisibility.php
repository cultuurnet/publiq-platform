<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum KeyVisibility: string
{
    case v1 = 'v1';
    case v2 = 'v2';
    case all = 'all';
}
