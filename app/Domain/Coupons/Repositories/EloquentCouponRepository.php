<?php

declare(strict_types=1);

namespace App\Domain\Coupons\Repositories;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Models\CouponModel;
use Ramsey\Uuid\UuidInterface;

final class EloquentCouponRepository implements CouponRepository
{
    public function save(Coupon $coupon): void
    {
        CouponModel::query()->create([
            'id' => $coupon->id->toString(),
            'integration_id' => $coupon->integrationId,
            'code' => $coupon->code,
        ]);
    }

    public function getById(UuidInterface $id): Coupon
    {
        /** @var CouponModel $couponModel */
        $couponModel = CouponModel::query()->findOrFail($id);

        return $couponModel->toDomain();
    }

    public function getByIntegrationId(UuidInterface $integrationId): ?Coupon
    {
        /** @var CouponModel $couponModel */
        $couponModel = CouponModel::query()->where('integration_id', $integrationId->toString())->firstOrFail();

        return $couponModel->toDomain();
    }
}
