<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\KeyVisibilityUpgradeRequest;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class KeyVisibilityUpgradeMapper
{
    public static function map(KeyVisibilityUpgradeRequest $request, UuidInterface $integrationId): KeyVisibilityUpgrade
    {
        return new KeyVisibilityUpgrade(
            Uuid::uuid4(),
            $integrationId,
            KeyVisibility::from($request->input('keyVisibility'))
        );
    }
}
