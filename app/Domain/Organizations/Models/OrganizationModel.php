<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Organization;
use App\Insightly\Models\InsightlyMappingModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class OrganizationModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'organizations';

    protected $fillable = [
        'id',
        'name',
        'invoice_email',
        'vat',
        'street',
        'zip',
        'city',
        'country',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn ($organizationModel) => OrganizationCreated::dispatch(Uuid::fromString($organizationModel->id))
        );
        self::updated(
            static fn ($organizationModel) => OrganizationUpdated::dispatch(Uuid::fromString($organizationModel->id))
        );
        self::deleted(
            static fn ($organizationModel) => OrganizationDeleted::dispatch(Uuid::fromString($organizationModel->id))
        );
    }

    /**
     * @return HasMany<InsightlyMappingModel, $this>
     */
    public function insightlyMappings(): HasMany
    {
        return $this->hasMany(InsightlyMappingModel::class, 'id');
    }

    public function insightlyId(): ?string
    {
        return $this->insightlyMappings()->first()->insightly_id ?? null;
    }

    public function toDomain(): Organization
    {
        return new Organization(
            Uuid::fromString($this->id),
            $this->name,
            $this->invoice_email,
            $this->vat,
            new Address(
                $this->street ?: '',
                $this->zip ?: '',
                $this->city ?: '',
                $this->country ?: '',
            ),
        );
    }
}
