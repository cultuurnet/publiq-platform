<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum IntegrationPartnerStatus: string
{
    case FIRST_PARTY = 'First party';
    case THIRD_PARTY = 'Third party';
}
