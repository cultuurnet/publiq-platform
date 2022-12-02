<?php

declare(strict_types=1);

namespace App\Insightly\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

final class InsightlyMappingModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'insightly_mappings';

    protected $fillable = [
        'id',
        'insightly_id',
        'resource_type',
    ];
}
