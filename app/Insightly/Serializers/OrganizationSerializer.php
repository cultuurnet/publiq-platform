<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Organizations\Organization;
use App\Insightly\Serializers\CustomFields\InvoiceEmailSerializer;
use App\Insightly\Serializers\CustomFields\VatSerializer;

final class OrganizationSerializer
{
    /** @return array<string, array|string> */
    public function toInsightlyArray(Organization $organization): array
    {
        $organizationAsArray = [
            'ORGANISATION_NAME' => $organization->name,
            'ADDRESS_BILLING_STREET' => $organization->address->street,
            'ADDRESS_BILLING_POSTCODE' => $organization->address->zip,
            'ADDRESS_BILLING_CITY' => $organization->address->city,
            'CUSTOMFIELDS' => [
                (new InvoiceEmailSerializer())->toInsightlyArray($organization->invoiceEmail),
            ],
        ];

        if ($organization->vat) {
            $organizationAsArray['CUSTOMFIELDS'][] = (new VatSerializer())->toInsightlyArray($organization->vat);
        }

        return $organizationAsArray;
    }

    /** @return array<string, array|string|int> */
    public function toInsightlyArrayForUpdate(Organization $organization, int $insightlyId): array
    {
        $organizationsArray = $this->toInsightlyArray($organization);
        $organizationsArray['ORGANIZATION_ID'] = $insightlyId;

        return $organizationsArray;
    }
}
