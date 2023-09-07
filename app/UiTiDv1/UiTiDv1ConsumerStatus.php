<?php

declare(strict_types=1);

namespace App\UiTiDv1;

enum UiTiDv1ConsumerStatus: string
{
    case Active = 'ACTIVE';
    case Blocked = 'BLOCKED';
    case Unknown = 'UNKNOWN';
}
