<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

final class OwnerModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'owners';

    protected $fillable = [
        'id',
        'auth0_id',
        'integration_id',
        'owner_type',
    ];
}
