<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum IntegrationUrlType: string
{
    case Login = 'login';
    case Callback = 'callback';
    case Logout = 'logout';
}
