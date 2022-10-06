<?php

declare(strict_types=1);

namespace App\Domain\Contacts;

enum ContactType: string
{
    case Technical = 'technical';
    case Organization = 'organization';
}
