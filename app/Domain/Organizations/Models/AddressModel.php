<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AddressModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'address';

    protected $fillable = [
        'id',
        'organization_id',
        'street',
        'zip',
        'city',
        'country',
    ];
}
