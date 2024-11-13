<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationUrlCreated;
use App\Domain\Integrations\Events\IntegrationUrlDeleted;
use App\Domain\Integrations\Events\IntegrationUrlUpdated;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

final class IntegrationUrlModel extends UuidModel
{
    protected $table = 'integrations_urls';

    protected $fillable = [
        'id',
        'integration_id',
        'environment',
        'type',
        'url',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn (IntegrationUrlModel $integrationUrlModel) => IntegrationUrlCreated::dispatch(
                Uuid::fromString($integrationUrlModel->integration_id),
                Uuid::fromString($integrationUrlModel->id),
            )
        );
        self::updated(
            static fn (IntegrationUrlModel $integrationUrlModel) => IntegrationUrlUpdated::dispatch(
                Uuid::fromString($integrationUrlModel->integration_id),
                Uuid::fromString($integrationUrlModel->id),
            )
        );
        self::deleted(
            static fn (IntegrationUrlModel $integrationUrlModel) => IntegrationUrlDeleted::dispatch(
                Uuid::fromString($integrationUrlModel->integration_id),
                Uuid::fromString($integrationUrlModel->id),
            )
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, $this>
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
