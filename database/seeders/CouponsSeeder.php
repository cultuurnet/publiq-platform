<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

final class CouponsSeeder extends Seeder
{
    public function run(CouponRepository $couponRepository): void
    {
        $couponId = Uuid::fromString('4cdb554f-9ed7-4370-8d83-436c77df896c');

        try {
            $couponRepository->getById($couponId);
            $this->command->info('Coupon already exists');
            return;
        } catch (ModelNotFoundException) {
        }

        $coupon = new Coupon(
            $couponId,
            null,
            '12345678901',
            false
        );

        $couponRepository->save($coupon);
    }
}
