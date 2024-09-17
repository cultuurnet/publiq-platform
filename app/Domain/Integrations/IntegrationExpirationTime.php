<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum IntegrationExpirationTime: int
{
    case SearchApi = 6;
    case Widgets = 3;
}
