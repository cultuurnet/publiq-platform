<?php

declare(strict_types=1);

namespace App\UiTiDv1\Models;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Models\UuidModel;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class UiTiDv1ConsumerModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'uitidv1_consumers';

    protected $fillable = [
        'id',
        'integration_id',
        'consumer_id',
        'consumer_key',
        'consumer_secret',
        'api_key',
        'environment',
    ];

    public function toDomain(): UiTiDv1Consumer
    {
        return new UiTiDv1Consumer(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            (string)$this->consumer_id,
            $this->consumer_key,
            $this->consumer_secret,
            $this->api_key,
            UiTiDv1Environment::from($this->environment)
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
