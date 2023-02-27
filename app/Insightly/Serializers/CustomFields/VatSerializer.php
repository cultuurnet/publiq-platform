<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

final class VatSerializer
{
    public const CUSTOM_FIELD_VAT = 'BTW_nummer__c';

    public function toInsightlyArray(string $vat): array
    {
        return [
            'FIELD_NAME' => self::CUSTOM_FIELD_VAT,
            'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_VAT,
            'FIELD_VALUE' => $vat,
        ];
    }
}
