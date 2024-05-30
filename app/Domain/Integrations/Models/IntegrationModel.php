<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Website;
use App\Domain\KeyVisibilityUpgrades\Models\KeyVisibilityUpgradeModel;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Insightly\Models\InsightlyMappingModel;
use App\Insightly\Resources\ResourceType;
use App\Models\UuidModel;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @property CouponModel|null $coupon
 * @property SubscriptionModel|null $subscription
 * @property KeyVisibilityUpgradeModel|null $keyVisibilityUpgrade
 * @property string $website
 */
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
        'organization_id',
        'status',
        'partner_status',
        'key_visibility',
        'website',
    ];

    protected $attributes = [
        'status' => IntegrationStatus::Draft,
        'partner_status' => IntegrationPartnerStatus::THIRD_PARTY,
    ];

    public function canBeActivated(): bool
    {
        return $this->status === IntegrationStatus::Draft->value
            || $this->status === IntegrationStatus::Blocked->value;
    }

    public function canBeApproved(): bool
    {
        return $this->status === IntegrationStatus::PendingApprovalIntegration->value;
    }

    public function canBeBlocked(): bool
    {
        return $this->status !== IntegrationStatus::Blocked->value;
    }

    public function canBeUnblocked(): bool
    {
        return $this->status === IntegrationStatus::Blocked->value;
    }

    public function isWidgets(): bool
    {
        return $this->type === IntegrationType::Widgets->value;
    }

    protected static function booted(): void
    {
        self::created(
            static fn (IntegrationModel $integrationModel) => IntegrationCreated::dispatch(Uuid::fromString($integrationModel->id))
        );
        self::updated(
            static fn (IntegrationModel $integrationModel) => IntegrationUpdated::dispatch(Uuid::fromString($integrationModel->id))
        );
        self::softDeleted(
            static fn (IntegrationModel $integrationModel) => IntegrationDeleted::dispatch(Uuid::fromString($integrationModel->id))
        );
    }

    public function delete(): ?bool
    {
        $this->update(['status' => IntegrationStatus::Deleted]);
        return parent::delete();
    }

    public function requestActivation(UuidInterface $organizationId): void
    {
        $this->update([
            'organization_id' => $organizationId->toString(),
            'status' => IntegrationStatus::PendingApprovalIntegration,
        ]);
        IntegrationActivationRequested::dispatch(Uuid::fromString($this->id));
    }

    public function activate(): void
    {
        $this->update([
            'status' => IntegrationStatus::Active,
        ]);
        IntegrationActivated::dispatch(Uuid::fromString($this->id));
    }

    public function activateWithOrganization(UuidInterface $organizationId): void
    {
        $this->update([
            'organization_id' => $organizationId->toString(),
            'status' => IntegrationStatus::Active,
        ]);
        IntegrationActivated::dispatch(Uuid::fromString($this->id));
    }

    public function approve(): void
    {
        $this->update([
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function block(): void
    {
        IntegrationPreviousStatusModel::query()->create(
            [
                'id' => Uuid::uuid4()->toString(),
                'integration_id' => $this->id,
                'status' => $this->status,
            ]
        );
        $this->update([
            'status' => IntegrationStatus::Blocked,
        ]);
        IntegrationBlocked::dispatch(Uuid::fromString($this->id));
    }

    public function unblock(): void
    {
        $integrationPreviousStatusBuilder = IntegrationPreviousStatusModel::query()
            ->where('integration_id', '=', $this->id);

        if ($integrationPreviousStatusBuilder->count() === 0) {
            $this->update([
                'status' => IntegrationStatus::Draft,
            ]);
        } else {
            /** @var IntegrationPreviousStatusModel $integrationPreviousStatusModel */
            $integrationPreviousStatusModel = $integrationPreviousStatusBuilder->first();
            $this->update([
                'status' => $integrationPreviousStatusModel->status,
            ]);
            $integrationPreviousStatusModel->delete();
        }
        IntegrationUnblocked::dispatch(Uuid::fromString($this->id));
    }

    /**
     * @return HasOne<KeyVisibilityUpgradeModel>
     */
    public function keyVisibilityUpgrade(): HasOne
    {
        return $this->hasOne(KeyVisibilityUpgradeModel::class, 'integration_id');
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
     * @return BelongsTo<OrganizationModel, IntegrationModel>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationModel::class, 'organization_id');
    }

    /**
     * @return HasMany<InsightlyMappingModel>
     */
    public function insightlyMappings(): HasMany
    {
        return $this->hasMany(InsightlyMappingModel::class, 'id');
    }

    /**
     * @return HasMany<IntegrationUrlModel>
     */
    public function urls(): HasMany
    {
        return $this->hasMany(IntegrationUrlModel::class, 'integration_id');
    }

    public function insightlyOpportunityId(): ?string
    {
        return $this->insightlyMappings()
            ->where('resource_type', ResourceType::Opportunity->value)
            ->first()
            ->insightly_id ?? null;
    }

    public function insightlyProjectId(): ?string
    {
        return $this->insightlyMappings()
            ->where('resource_type', ResourceType::Project->value)
            ->first()
            ->insightly_id ?? null;
    }

    /**
     * @return HasOne<CouponModel>
     */
    public function coupon(): HasOne
    {
        return $this->hasOne(CouponModel::class, 'integration_id');
    }

    public function couponCode(): ?string
    {
        return $this->coupon->code ?? null;
    }

    public function couponId(): ?string
    {
        return $this->coupon->id ?? null;
    }

    /**
     * @return HasMany<Auth0ClientModel>
     */
    public function auth0Clients(): HasMany
    {
        return $this->hasMany(Auth0ClientModel::class, 'integration_id');
    }

    /**
     * @return HasMany<UiTiDv1ConsumerModel>
     */
    public function uiTiDv1Consumers(): HasMany
    {
        return $this->hasMany(UiTiDv1ConsumerModel::class, 'integration_id');
    }

    public function hasMissingAuth0Clients(): bool
    {
        return $this->auth0Clients()->count() < count(Auth0Tenant::cases());
    }

    public function hasMissingUiTiDv1Consumers(): bool
    {
        return $this->uiTiDv1Consumers()->count() < count(UiTiDv1Environment::cases());
    }

    public function toDomain(): Integration
    {
        $foundOrganization = $this->organization()->first();

        $integration = (new Integration(
            Uuid::fromString($this->id),
            IntegrationType::from($this->type),
            $this->name,
            $this->description,
            Uuid::fromString($this->subscription_id),
            IntegrationStatus::from($this->status),
            IntegrationPartnerStatus::from($this->partner_status),
        ))->withKeyVisibility(
            KeyVisibility::from($this->key_visibility)
        )->withContacts(
            ...$this->contacts()
            ->get()
            ->map(fn (ContactModel $contactModel) => $contactModel->toDomain())
            ->toArray()
        )->withUrls(
            ...$this->urls()
            ->get()
            ->map(fn (IntegrationUrlModel $integrationUrlModel) => $integrationUrlModel->toDomain())
            ->toArray()
        )->withUiTiDv1Consumers(
            ...$this->uiTiDv1Consumers()
            ->get()
            ->map(fn (UiTiDv1ConsumerModel $uiTiDv1ConsumerModel) => $uiTiDv1ConsumerModel->toDomain())
            ->toArray()
        )->withAuth0Clients(
            ...$this->auth0Clients()
            ->get()
            ->map(fn (Auth0ClientModel $auth0ClientModel) => $auth0ClientModel->toDomain())
            ->toArray()
        );

        if ($this->keyVisibilityUpgrade) {
            $integration = $integration->withKeyVisibilityUpgrade($this->keyVisibilityUpgrade->toDomain());
        }

        if ($this->subscription) {
            $integration = $integration->withSubscription($this->subscription->toDomain());
        }

        if ($this->website) {
            $integration = $integration->withWebsite(new Website($this->website));
        }

        if ($this->coupon) {
            $integration = $integration->withCoupon($this->coupon->toDomain());
        }

        if ($foundOrganization !== null) {
            $integration = $integration->withOrganization($foundOrganization->toDomain());
        }

        return $integration;
    }
}
