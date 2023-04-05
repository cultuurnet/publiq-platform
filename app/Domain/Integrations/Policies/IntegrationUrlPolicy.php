<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Integrations\Models\IntegrationUrlModel;

final class IntegrationUrlPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, IntegrationUrlModel $integrationUrlModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, IntegrationUrlModel $integrationUrlModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, IntegrationUrlModel $integrationUrlModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, IntegrationUrlModel $integrationUrlModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, IntegrationUrlModel $integrationUrlModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, IntegrationUrlModel $integrationUrlModel): bool
    {
        return false;
    }
}
