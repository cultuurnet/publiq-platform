<?php

declare(strict_types=1);

namespace App\Domain\Coupons\Models;

use App\Domain\Coupons\Coupon;
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
        'integration_id',
        'code',
        'is_used',
    ];

    protected $casts = [
        'is_used' => 'boolean',
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
            $this->integration_id !== null ? Uuid::fromString($this->integration_id) : null,
            $this->code,
            $this->is_used
        );
    }
}
