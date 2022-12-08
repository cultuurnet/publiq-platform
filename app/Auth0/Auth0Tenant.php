<?php

declare(strict_types=1);

namespace App\Auth0;

enum Auth0Tenant: string
{
    case Acc = 'acc';
    case Test = 'test';
    case Prod = 'prod';
}
