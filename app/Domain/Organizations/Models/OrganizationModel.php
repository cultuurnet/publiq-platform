<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
