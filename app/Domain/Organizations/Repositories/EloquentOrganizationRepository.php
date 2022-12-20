<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Organization;

final class EloquentOrganizationRepository implements OrganizationRepository
{
    public function save(Organization $organization): void
    {
        OrganizationModel::query()->create([
            'id' => $organization->id->toString(),
            'name' => $organization->name,
            'vat' => $organization->vat,
            'street' => $organization->address?->street,
            'zip' => $organization->address?->zip,
            'city' => $organization->address?->city,
            'country' => $organization->address?->country,
        ]);
    }
}
