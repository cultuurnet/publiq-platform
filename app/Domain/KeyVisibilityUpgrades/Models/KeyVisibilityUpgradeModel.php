<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades\Models;

use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\KeyVisibilityUpgrades\Events\KeyVisibilityUpgradeCreated;
use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use App\Models\UuidModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property Carbon $created_at
 */
final class KeyVisibilityUpgradeModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'key_visibility_upgrades';

    protected $fillable = [
        'id',
        'integration_id',
        'key_visibility',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn (KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel)
                => KeyVisibilityUpgradeCreated::dispatch(Uuid::fromString($keyVisibilityUpgradeModel->id))
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function toDomain(): KeyVisibilityUpgrade
    {
        return (new KeyVisibilityUpgrade(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            KeyVisibility::from($this->key_visibility)
        ))->withCreatedAt($this->created_at->toDateTimeImmutable());
    }
}
