<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $status
 */
final class IntegrationPreviousStatusModel extends UuidModel
{
    protected $table = 'integrations_previous_statuses';

    protected $fillable = [
        'integration_id',
        'status',
    ];

    /**
     * @return BelongsTo<IntegrationModel, IntegrationPreviousStatusModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
