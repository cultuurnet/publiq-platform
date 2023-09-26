<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\UpdateBillingInfoRequest;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use Ramsey\Uuid\Uuid;

final class UpdateBillingInfoMapper
{
    public static function map(UpdateBillingInfoRequest $request): Organization
    {
        return new Organization(
            Uuid::fromString($request->input('organisation.id')),
            $request->input('organisation.name'),
            'test@test.be',
            $request->input('organisation.vat'),
            new Address(
                $request->input('organisation.address.street'),
                $request->input('organisation.address.zip'),
                $request->input('organisation.address.city'),
                $request->input('organisation.address.country'),
            )
        );
    }
}
