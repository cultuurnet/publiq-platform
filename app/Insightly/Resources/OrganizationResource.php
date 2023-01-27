<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Organizations\Organization;

interface OrganizationResource
{
    public function create(Organization $organization): int;

    public function update(Organization $organization, int $id): void;

    public function delete(int $id): void;
}
