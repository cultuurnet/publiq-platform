<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Repositories;

use App\Domain\Organizations\Models\AddressModel;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Organization;
use Illuminate\Support\Facades\DB;

final class OrganizationRepository
{
    public function save(Organization $organization): void
    {
        DB::transaction(static function () use ($organization): void {
            OrganizationModel::query()->create([
                'id' => $organization->id->toString(),
                'name' => $organization->name,
                'vat' => $organization->vat,
            ]);

            if ($organization->address) {
                AddressModel::query()->create([
                    'id' => $organization->address->id->toString(),
                    'organization_id' => $organization->id->toString(),
                    'street' => $organization->address->street,
                    'zip' => $organization->address->zip,
                    'city' => $organization->address->city,
                    'country' => $organization->address->country,
                ]);
            }
        });
    }
}
