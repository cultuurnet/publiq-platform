<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SubscriptionModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'subscriptions';

    protected $fillable = [
        'id',
        'name',
        'description',
        'currency',
        'price',
        'billing_interval',
        'fee',
    ];
}
