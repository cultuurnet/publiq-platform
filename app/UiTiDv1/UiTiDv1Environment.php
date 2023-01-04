<?php

declare(strict_types=1);

namespace App\UiTiDv1;

enum UiTiDv1Environment: string
{
    case Acceptance = 'acc';
    case Testing = 'test';
    case Production = 'prod';
}
