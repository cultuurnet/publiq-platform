<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Integrations\Models\IntegrationModel;

final class IntegrationPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, IntegrationModel $integrationModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, IntegrationModel $integrationModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, IntegrationModel $integrationModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, IntegrationModel $integrationModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, IntegrationModel $integrationModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, IntegrationModel $integrationModel): bool
    {
        return false;
    }
}
