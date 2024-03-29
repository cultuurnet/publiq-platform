<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Coupons\Models\CouponModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

final class DistributeCoupon extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function handle(ActionFields $fields, Collection $coupons): void
    {
        /** @var CouponModel $coupon */
        foreach ($coupons as $coupon) {
            $coupon->distribute();
        }
    }
}
