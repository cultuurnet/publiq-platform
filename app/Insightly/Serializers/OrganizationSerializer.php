<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Organizations\Organization;

final class OrganizationSerializer
{
    /** @return array<string, string> */
    public function toInsightlyArray(Organization $organization): array
    {
        $organizationAsArray = [
            'ORGANISATION_NAME' => $organization->name,
            'ADDRESS_BILLING_STREET' => $organization->address->street,
            'ADDRESS_BILLING_POSTCODE' => $organization->address->zip,
            'ADDRESS_BILLING_CITY' => $organization->address->street,
        ];

        if ($organization->vat) {
            $organizationAsArray['CUSTOMFIELDS'] = (new VatSerializer())->toInsightlyArray($organization->vat);
        }

        return $organizationAsArray;
    }
}
