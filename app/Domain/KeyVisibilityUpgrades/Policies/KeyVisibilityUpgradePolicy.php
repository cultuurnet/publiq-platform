<?php

declare(strict_types=1);

namespace App\Domain\KeyVisibilityUpgrades\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\KeyVisibilityUpgrades\Models\KeyVisibilityUpgradeModel;

final class KeyVisibilityUpgradePolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, KeyVisibilityUpgradeModel $keyVisibilityUpgradeModel): bool
    {
        return false;
    }
}
