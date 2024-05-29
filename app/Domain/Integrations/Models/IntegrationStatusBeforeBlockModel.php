<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationStatusBeforeBlock;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

final class IntegrationStatusBeforeBlockModel extends UuidModel
{
    protected $table = 'integration_status_before_block';

    protected $fillable = [
        'integration_id',
        'status',
    ];

    /**
     * @return BelongsTo<IntegrationModel, IntegrationStatusBeforeBlockModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function toDomain(): IntegrationStatusBeforeBlock
    {
        return new IntegrationStatusBeforeBlock(
            Uuid::fromString($this->integration_id),
            IntegrationStatus::from($this->status)
        );
    }
}
