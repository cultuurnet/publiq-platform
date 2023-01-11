<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Insightly\Models\InsightlyMappingModel;
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
        'status',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn ($integrationModel) => IntegrationCreated::dispatch(Uuid::fromString($integrationModel->id))
        );
    }

    public function delete(): ?bool
    {
        $this->update(['status' => IntegrationStatus::Deleted]);
        return parent::delete();
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
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(SubscriptionModel::class, 'subscription_id');
    }

    /**
     * @return BelongsTo<InsightlyMappingModel, IntegrationModel>
     */
    public function insightlyMapping(): BelongsTo
    {
        return $this->belongsTo(InsightlyMappingModel::class, 'id');
    }

    public function insightlyId(): ?string
    {
        return $this->insightlyMapping ? $this->insightlyMapping->insightly_id : null;
    }

    public function toDomain(): Integration
    {
        return new Integration(
            Uuid::fromString($this->id),
            IntegrationType::from($this->type),
            $this->name,
            $this->description,
            Uuid::fromString($this->subscription_id),
            IntegrationStatus::from($this->status),
            $this->contacts()
                ->get()
                ->map(fn (ContactModel $contactModel) => $contactModel->toDomain())
                ->toArray()
        );
    }
}
