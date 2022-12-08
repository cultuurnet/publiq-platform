<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

final class IntegrationModel extends UuidModel
{
    protected $table = 'integrations';

    protected $fillable = [
        'id',
        'type',
        'name',
        'description',
        'subscription_id',
        'status',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn ($integrationModel) => IntegrationCreated::dispatch(Uuid::fromString($integrationModel->id))
        );
    }
    public function delete()
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->update(['status' => IntegrationStatus::Deleted]);
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

    public function toDomain(): Integration
    {
        return new Integration(
            Uuid::fromString($this->id),
            IntegrationType::from($this->type),
            $this->name,
            $this->description,
            Uuid::fromString($this->subscription_id),
            $this->contacts()
                ->get()
                ->map(fn (ContactModel $contactModel) => $contactModel->toDomain())
                ->toArray(),
            IntegrationStatus::from($this->status)
        );
    }
}
