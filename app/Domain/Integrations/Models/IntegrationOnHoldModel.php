<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\IntegrationOnHold;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $integration_id
 * @property bool $on_hold
 * @property string $comment
 * @mixin Builder
 * */
final class IntegrationOnHoldModel extends UuidModel
{
    protected $table = 'integrations_on_hold';

    protected $fillable = [
        'id',
        'integration_id',
        'on_hold',//boolean
        'comment',
    ];

    /**
     * @return BelongsTo<IntegrationModel, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function toDomain(): IntegrationOnHold
    {
        return new IntegrationOnHold(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            $this->comment,
            $this->on_hold
        );
    }
}
