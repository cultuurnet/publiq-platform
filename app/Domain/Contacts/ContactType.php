<?php

declare(strict_types=1);

namespace App\Domain\Contacts;

enum ContactType: string
{
    case Organization = 'organization';
    case Technical = 'technical';
    case Contributor = 'contributor';
}
