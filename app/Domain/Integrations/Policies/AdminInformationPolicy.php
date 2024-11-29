<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Integrations\Models\AdminInformationModel;

final class AdminInformationPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, AdminInformationModel $adminInformationModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, AdminInformationModel $adminInformationModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, AdminInformationModel $adminInformationModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, AdminInformationModel $adminInformationModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, AdminInformationModel $adminInformationModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, AdminInformationModel $adminInformationModel): bool
    {
        return false;
    }
}
