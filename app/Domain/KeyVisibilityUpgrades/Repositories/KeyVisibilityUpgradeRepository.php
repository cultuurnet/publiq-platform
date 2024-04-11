<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades\Repositories;

use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use Ramsey\Uuid\UuidInterface;

interface KeyVisibilityUpgradeRepository
{
    public function save(KeyVisibilityUpgrade $keyVisibilityUpgrade): void;

    public function getById(UuidInterface $id): KeyVisibilityUpgrade;
}
