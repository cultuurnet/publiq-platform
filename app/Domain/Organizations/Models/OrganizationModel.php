<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class OrganizationModel extends Model
{
    use SoftDeletes;

    protected $table = 'organization';

    protected $fillable = [
        'id',
        'name',
        'vat',
    ];

    /**
     * @return HasOne<AddressModel>
     */
    public function address(): HasOne
    {
        return $this->hasOne(AddressModel::class, 'organization_id');
    }
}
