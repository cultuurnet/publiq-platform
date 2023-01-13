<?php

declare(strict_types=1);

namespace App\Domain\Coupons;

enum CouponStatus: string
{
    case Free = 'free';
    case Distributed  = 'distributed';
    case Used = 'used';
}
