<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationCreated;
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
        'vat',
        'street',
        'zip',
        'city',
        'country',
    ];

    public function toDomain(): Organization
    {
        $address = new Address(
            $this->street ?: '',
            $this->zip ?: '',
            $this->city ?: '',
            $this->country ?: '',
        );

        return new Organization(
            Uuid::fromString($this->id),
            $this->name,
            $this->vat,
            $address->isEmpty() ? null : $address,
        );
    }
}
