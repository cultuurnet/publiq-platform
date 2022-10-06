<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SubscriptionModel extends Model
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
}
