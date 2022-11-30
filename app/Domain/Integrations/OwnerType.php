<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

enum OwnerType: string
{
    case Integrator = 'integrator';
    case Collaborator = 'collaborator';
}
