<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Organization;

interface OrganizationRepository
{
    public function save(Organization $organization): void;
}
