<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AddressModel extends Model
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
