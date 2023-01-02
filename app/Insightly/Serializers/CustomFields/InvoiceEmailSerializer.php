<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

final class InvoiceEmailSerializer
{
    private const CUSTOM_FIELD_INVOICE_EMAIL = 'Email_boekhouding__c';

    public function toInsightlyArray(string $invoiceEmail): array
    {
        return [
            'FIELD_NAME' => self::CUSTOM_FIELD_INVOICE_EMAIL,
            'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_INVOICE_EMAIL,
            'FIELD_VALUE' => $invoiceEmail,
        ];
    }
}
