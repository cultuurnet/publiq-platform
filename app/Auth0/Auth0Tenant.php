<?php

declare(strict_types=1);

namespace App\Auth0;

enum Auth0Tenant: string
{
    case Acceptance = 'acc';
    case Testing = 'test';
    case Production = 'prod';
}
