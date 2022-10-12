<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SubscriptionModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'subscription';

    protected $fillable = [
        'id',
        'name',
        'description',
        'currency',
        'price',
        'billing_interval',
        'fee',
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (float) $value / 100,
            set: static fn ($value) => (int) ($value * 100),
        );
    }

    protected function fee(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (float) $value / 100,
            set: static fn ($value) => (int) ($value * 100),
        );
    }
}
