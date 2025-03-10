<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationApproved;
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
use App\Keycloak\Models\KeycloakClientModel;
use App\Mails\Template\TemplateName;
use App\Models\UuidModel;
use App\Nova\Filters\AdminInformationFilter;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Database\Eloquent\Builder;
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
 * @property IntegrationType $type
 * @property IntegrationStatus $status
 * @property IntegrationPartnerStatus $partner_status
 * @property KeyVisibility $key_visibility
 * @property string $website
 * @method static Builder|IntegrationModel withoutMailSent(TemplateName $templateName)
 * @method static Builder|IntegrationModel notOnHold()
 * @mixin Builder
 * */
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

    protected $casts = [
        'type' => IntegrationType::class,
        'status' => IntegrationStatus::class,
        'partner_status' => IntegrationPartnerStatus::class,
        'key_visibility' => KeyVisibility::class,
    ];

    public function canBeActivated(): bool
    {
        return $this->status === IntegrationStatus::Draft
            || $this->status === IntegrationStatus::Blocked;
    }

    public function canBeApproved(): bool
    {
        return $this->status === IntegrationStatus::PendingApprovalIntegration;
    }

    public function canBeBlocked(): bool
    {
        return $this->status !== IntegrationStatus::Blocked;
    }

    public function canBeUnblocked(): bool
    {
        return $this->status === IntegrationStatus::Blocked;
    }

    public function isWidgets(): bool
    {
        return $this->type === IntegrationType::Widgets;
    }

    public function isUiTPAS(): bool
    {
        return $this->type === IntegrationType::UiTPAS;
    }

    protected static function booted(): void
    {
        self::creating(
            static function (IntegrationModel $integrationModel) {
                if ($integrationModel->type == IntegrationType::UiTPAS) {
                    $integrationModel->key_visibility = KeyVisibility::v2;
                }
            }
        );
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
        // Temporarily disable events to avoid triggering 'updated'
        static::withoutEvents(function () {
            $this->update(['status' => IntegrationStatus::Deleted]);
        });
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
        IntegrationApproved::dispatch(Uuid::fromString($this->id));
    }

    public function block(): void
    {
        IntegrationPreviousStatusModel::query()->create(
            [
                'id' => $this->id,
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
        /** @var ?IntegrationPreviousStatusModel $integrationPreviousStatus */
        $integrationPreviousStatus = IntegrationPreviousStatusModel::query()->find($this->id);

        $this->update([
            'status' => $integrationPreviousStatus ? $integrationPreviousStatus->status : IntegrationStatus::Draft,
        ]);

        $integrationPreviousStatus?->delete();

        IntegrationUnblocked::dispatch(
            Uuid::fromString($this->id)
        );
    }

    /**
     * @return HasOne<KeyVisibilityUpgradeModel, $this>
     */
    public function keyVisibilityUpgrade(): HasOne
    {
        return $this->hasOne(KeyVisibilityUpgradeModel::class, 'integration_id');
    }

    /**
     * @return HasMany<UdbOrganizerModel, $this>
     */
    public function udbOrganizers(): HasMany
    {
        return $this->hasMany(UdbOrganizerModel::class, 'integration_id');
    }

    /**
     * @return HasMany<ContactModel, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ContactModel::class, 'integration_id');
    }

    /**
     * Tracks which mails have been sent about this integration
     * @return HasMany<IntegrationMailModel, $this>
     */
    public function mail(): HasMany
    {
        return $this->hasMany(IntegrationMailModel::class, 'integration_id');
    }

    /**
     * @return BelongsTo<SubscriptionModel, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(SubscriptionModel::class, 'subscription_id');
    }

    /**
     * @return BelongsTo<OrganizationModel, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationModel::class, 'organization_id');
    }

    /**
     * @return HasMany<InsightlyMappingModel, $this>
     */
    public function insightlyMappings(): HasMany
    {
        return $this->hasMany(InsightlyMappingModel::class, 'id');
    }

    /**
     * @return HasMany<IntegrationUrlModel, $this>
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
     * @return HasOne<CouponModel, $this>
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
     * @return HasOne<AdminInformationModel, $this>
     */
    public function adminInformation(): HasOne
    {
        return $this->hasOne(AdminInformationModel::class, 'integration_id');
    }

    /**
     * @return HasMany<UiTiDv1ConsumerModel, $this>
     */
    public function uiTiDv1Consumers(): HasMany
    {
        return $this->hasMany(UiTiDv1ConsumerModel::class, 'integration_id');
    }

    /**
     * @return HasMany<KeycloakClientModel, $this>
     */
    public function keycloakClients(): HasMany
    {
        return $this->hasMany(KeycloakClientModel::class, 'integration_id');
    }

    public function hasMissingUiTiDv1Consumers(): bool
    {
        return $this->uiTiDv1Consumers()->count() < count(UiTiDv1Environment::cases());
    }

    public function hasMissingKeycloakConsumers(): bool
    {
        return $this->keycloakClients()->count() < count(Environment::cases());
    }

    public function toDomain(): Integration
    {
        $foundOrganization = $this->organization()->first();

        $integration = (new Integration(
            Uuid::fromString($this->id),
            $this->type,
            $this->name,
            $this->description,
            Uuid::fromString($this->subscription_id),
            $this->status,
            $this->partner_status,
        ))->withKeyVisibility(
            $this->key_visibility
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
        )->withKeycloakClients(
            ...$this->keycloakClients()
            ->get()
            ->map(fn (KeycloakClientModel $keycloakClientModel) => $keycloakClientModel->toDomain())
            ->toArray()
        )->withUdbOrganizers(
            ...$this->udbOrganizers()
            ->get()
            ->map(fn (UdbOrganizerModel $organizerModel) => $organizerModel->toDomain())
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

    public function scopeWithoutMailSent(Builder $query, TemplateName $templateName): Builder
    {
        return $query->whereDoesntHave('mail', function (Builder $query) use ($templateName) {
            $query->where('template_name', $templateName->value);
        });
    }

    public function scopeNotOnHold(Builder $query): Builder
    {
        return $query->whereDoesntHave('adminInformation', function ($q) {
            $q->where(AdminInformationFilter::ON_HOLD_COLUMN, '=', 1);
        });
    }
}
