<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

use App\Domain\Coupons\Coupon;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;

final class SubscriptionSerializer
{
    private const CUSTOM_FIELD_SUBSCRIPTION_PLAN = 'Subscription_plan__c';
    private const CUSTOM_FIELD_SUBSCRIPTION_PRICE = 'Subscription_price__c';
    private const CUSTOM_FIELD_SETUP_FEE = 'Setup_fee__c';

    public function toInsightlyArray(Subscription $subscription, ?Coupon $coupon): array
    {
        $insightlyArray = [
            [
                'FIELD_NAME' => self::CUSTOM_FIELD_SUBSCRIPTION_PLAN,
                'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_SUBSCRIPTION_PLAN,
                'FIELD_VALUE' => $subscription->category->value,
            ],
        ];

        if ($subscription->category !== SubscriptionCategory::Custom) {
            $insightlyArray[] = [
                'FIELD_NAME' => self::CUSTOM_FIELD_SETUP_FEE,
                'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_SETUP_FEE,
                'FIELD_VALUE' => $subscription->fee,
            ];

            $price = $subscription->price;
            if ($coupon) {
                $price = max($price - $coupon->reduction, 0);
            }

            $insightlyArray[] = [
                'FIELD_NAME' => self::CUSTOM_FIELD_SUBSCRIPTION_PRICE,
                'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_SUBSCRIPTION_PRICE,
                'FIELD_VALUE' => $price,
            ];
        }

        return $insightlyArray;
    }
}
