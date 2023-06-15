<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum Environment: string
{
    case Acceptance = 'acc';
    case Testing = 'test';
    case Production = 'prod';
}
