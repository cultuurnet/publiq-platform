<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades\Repositories;

use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use App\Domain\KeyVisibilityUpgrades\Models\KeyVisibilityUpgradeModel;
use Ramsey\Uuid\UuidInterface;

final class EloquentKeyVisibilityUpgradeRepository implements KeyVisibilityUpgradeRepository
{
    public function save(KeyVisibilityUpgrade $keyVisibilityUpgrade): void
    {
        KeyVisibilityUpgradeModel::query()->create(
            [
                'id' => $keyVisibilityUpgrade->id->toString(),
                'integration_id' => $keyVisibilityUpgrade->integrationId->toString(),
                'key_visibility' => $keyVisibilityUpgrade->keyVisibility->value,
            ]
        );
    }

    public function getById(UuidInterface $id): KeyVisibilityUpgrade
    {
        /** @var KeyVisibilityUpgradeModel $keyVisibilityUpgrade */
        $keyVisibilityUpgrade = KeyVisibilityUpgradeModel::query()->findOrFail($id->toString());
        return $keyVisibilityUpgrade->toDomain();
    }
}
