<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

final class IntegrationUrlModel extends UuidModel
{
    protected $table = 'integrations_urls';

    protected $fillable = [
        'integration_id',
        'environment',
        'type',
        'url',
    ];

    /**
     * @return BelongsTo<IntegrationModel, IntegrationUrlModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function toDomain(): IntegrationUrl
    {
        return new IntegrationUrl(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            Environment::from($this->environment),
            IntegrationUrlType::from($this->type),
            $this->url
        );
    }
}
