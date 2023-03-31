<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $category
 * @property string $integration_type
 * @property string $currency
 * @property int $price
 * @property int $fee
 */
final class SubscriptionModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'subscriptions';

    protected $fillable = [
        'id',
        'name',
        'description',
        'category',
        'currency',
        'integration_type',
        'price',
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

    public function toDomain(): Subscription
    {
        return new Subscription(
            Uuid::fromString($this->id),
            $this->name,
            $this->description,
            SubscriptionCategory::from($this->category),
            IntegrationType::from($this->integration_type),
            Currency::from($this->currency),
            $this->price,
            $this->fee
        );
    }
}
