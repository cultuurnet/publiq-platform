<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class OwnerModel extends Model
{
    use SoftDeletes;

    protected $table = 'owners';

    protected $fillable = [
        'owner_id',
        'integration_id',
        'owner_type',
    ];

    /**
     * @return BelongsTo<IntegrationModel, OwnerModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
