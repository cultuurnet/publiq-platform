<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Repositories;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Organizations\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EloquentOrganizationRepository implements OrganizationRepository
{
    public function save(Organization $organization): void
    {
        OrganizationModel::query()->updateOrCreate(
            [
                'id' => $organization->id->toString(),
            ],
            [
            'id' => $organization->id->toString(),
            'name' => $organization->name,
            'invoice_email' => $organization->invoiceEmail,
            'vat' => $organization->vat,
            'street' => $organization->address->street,
            'zip' => $organization->address->zip,
            'city' => $organization->address->city,
            'country' => $organization->address->country,
        ]
        );
    }

    public function getById(UuidInterface $id): Organization
    {
        /** @var OrganizationModel $organizationModel */
        $organizationModel = OrganizationModel::query()->findOrFail($id->toString());

        return $organizationModel->toDomain();
    }

    public function getByIntegrationId(UuidInterface $integrationId): Organization
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($integrationId->toString());

        if ($integrationModel->organization_id === null) {
            throw new ModelNotFoundException();
        }

        return $this->getById(Uuid::fromString($integrationModel->organization_id));
    }
}
