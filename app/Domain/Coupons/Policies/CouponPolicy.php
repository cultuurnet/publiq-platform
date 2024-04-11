<?php

declare(strict_types=1);

namespace App\Domain\Coupons\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Coupons\Models\CouponModel;

final class CouponPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, CouponModel $couponModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, CouponModel $couponModel): bool
    {
        return false;
    }

    public function delete(UserModel $userModel, CouponModel $couponModel): bool
    {
        return false;
    }

    public function restore(UserModel $userModel, CouponModel $couponModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, CouponModel $couponModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, CouponModel $couponModel): bool
    {
        return false;
    }
}
