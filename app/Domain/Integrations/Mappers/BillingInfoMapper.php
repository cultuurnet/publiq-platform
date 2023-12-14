<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\CreateBillingInfoRequest;
use App\Domain\Integrations\FormRequests\UpdateBillingInfoRequest;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class BillingInfoMapper
{
    public static function mapCreate(CreateBillingInfoRequest $request): Organization
    {
        return self::map($request);
    }

    public static function mapUpdate(UpdateBillingInfoRequest $request): Organization
    {
        return self::map($request);
    }

    private static function map(Request $request): Organization
    {
        return new Organization(
            $request->input('organisation.id') ? Uuid::fromString($request->input('organisation.id')) : Uuid::uuid4(),
            $request->input('organisation.name'),
            $request->input('organisation.invoiceEmail'),
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
