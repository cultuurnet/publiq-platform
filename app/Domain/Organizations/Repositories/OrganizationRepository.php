<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Organization;
use Ramsey\Uuid\UuidInterface;

interface OrganizationRepository
{
    public function save(Organization $organization): void;

    public function getById(UuidInterface $id): Organization;
}
