<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\CreateOrganizationRequest;
use App\Domain\Integrations\FormRequests\UpdateOrganizationRequest;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class OrganizationMapper
{
    public static function mapCreate(CreateOrganizationRequest $request): Organization
    {
        return self::map($request);
    }

    public static function mapUpdate(UpdateOrganizationRequest $request): Organization
    {
        return self::map($request);
    }

    private static function map(Request $request): Organization
    {
        return new Organization(
            $request->input('organization.id') ? Uuid::fromString($request->input('organization.id')) : Uuid::uuid4(),
            $request->input('organization.name'),
            $request->input('organization.invoiceEmail'),
            $request->input('organization.vat'),
            new Address(
                $request->input('organization.address.street'),
                $request->input('organization.address.zip'),
                $request->input('organization.address.city'),
                $request->input('organization.address.country'),
            )
        );
    }
}
