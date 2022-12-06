<?php

declare(strict_types=1);

namespace App\Domain\Contacts;

enum ContactType: string
{
    case Functional = 'functional';
    case Technical = 'technical';
    case Contributor = 'contributor';
}
