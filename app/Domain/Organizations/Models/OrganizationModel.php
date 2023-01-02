<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Organization;
use App\Models\UuidModel;
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
