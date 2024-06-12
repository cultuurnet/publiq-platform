<?php

declare(strict_types=1);

namespace App\Auth0;

enum Auth0Config: string
{
    case isEnabled = 'auth0.enabled';
}
