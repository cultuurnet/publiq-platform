<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class IntegrationModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'integrations';

    protected $fillable = [
        'id',
        'type',
        'name',
        'description',
        'subscription_id',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn ($integrationModel) => IntegrationCreated::dispatch(
            Uuid::fromString($integrationModel->id)
        )
        );
    }

    /**
     * @return HasMany<ContactModel>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ContactModel::class, 'integration_id');
    }

    /**
     * @return BelongsTo<SubscriptionModel, IntegrationModel>
     */
    public function subscription(): belongsTo
    {
        return $this->belongsTo(SubscriptionModel::class, 'subscription_id');
    }
}
