<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

final class CouponSerializer
{
    private const CUSTOM_FIELD_COUPON = 'Coupon__c';

    public function toInsightlyArray(string $couponCode): array
    {
        return [
            'FIELD_NAME' => self::CUSTOM_FIELD_COUPON,
            'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_COUPON,
            'FIELD_VALUE' => $couponCode,
        ];
    }
}
