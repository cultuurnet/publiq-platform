<?php

declare(strict_types=1);

namespace App\Insightly\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class InsightlyMappingModel extends Model
{
    use SoftDeletes;

    protected $table = 'insightly_mappings';

    protected $fillable = [
        'id',
        'insightly_id',
        'resource_type',
    ];
}
