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
        $couponList = [
            '4cdb554f-9ed7-4370-8d83-436c77df896c' => 'seed1234560',
            '0cd51dd8-0a73-4395-af63-68ff6ae14458' => 'seed1234561',
            'e2a6232d-6dc4-475c-8565-71312f133beb' => 'seed1234562',
            'e3efaa6c-634f-46bd-853e-e350d01a0ae9' => 'seed1234563',
            '59b896d1-294d-4002-9916-957edfac782e' => 'seed1234564',
            'fda7db7a-39ee-4608-9f13-523222667dc5' => 'seed1234565',
            'c84c5462-68bd-4324-899a-c617545b4c97' => 'seed1234566',
            'c0af639c-06cb-4cf9-81f8-77e1b154c4e6' => 'seed1234567',
            '91659504-2769-4d2d-aeb8-cd44eb880873' => 'seed1234568',
            '4ecd49c3-29ad-40ff-9c44-af7269cc4af0' => 'seed1234569',
        ];

        foreach ($couponList as $couponKey => $couponCode) {
            $couponId = Uuid::fromString($couponKey);

            try {
                $couponRepository->getById($couponId);
                $this->command->info('Coupon already exists');
                return;
            } catch (ModelNotFoundException) {
            }

            $coupon = new Coupon(
                $couponId,
                null,
                $couponCode
            );

            $couponRepository->save($coupon);
        }
    }
}
