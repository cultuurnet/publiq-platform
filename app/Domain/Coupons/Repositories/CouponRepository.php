<?php

declare(strict_types=1);

namespace App\Domain\Coupons\Repositories;

use App\Domain\Coupons\Coupon;
use Ramsey\Uuid\UuidInterface;

interface CouponRepository
{
    public function save(Coupon $coupon): void;

    public function getById(UuidInterface $id): Coupon;

    public function getByIntegrationId(UuidInterface $integrationId): Coupon;

    public function getByCode(string $code): Coupon;
}
