<?php

declare(strict_types=1);

namespace App\Domain\Coupons\Models;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\CouponStatus;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class CouponModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'id',
        'is_distributed',
        'integration_id',
        'code',
    ];

    protected $casts = [
        'is_distributed' => 'boolean',
    ];

    /**
     * @return BelongsTo<IntegrationModel, CouponModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function toDomain(): Coupon
    {
        return new Coupon(
            Uuid::fromString($this->id),
            $this->is_distributed,
            $this->integration_id !== null ? Uuid::fromString($this->integration_id) : null,
            $this->code,
            $this->getStatus()
        );
    }

    public function getStatus(): CouponStatus
    {
        if ($this->integration_id !== null) {
            return CouponStatus::Used;
        }
        if ($this->is_distributed) {
            return CouponStatus::Distributed;
        }
        return CouponStatus::Free;
    }
}
