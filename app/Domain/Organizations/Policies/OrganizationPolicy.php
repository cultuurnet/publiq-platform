<?php

declare(strict_types=1);

namespace App\Domain\Organizations\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Organizations\Models\OrganizationModel;

final class OrganizationPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, OrganizationModel $organizationModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, OrganizationModel $organizationModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, OrganizationModel $organizationModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, OrganizationModel $organizationModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, OrganizationModel $organizationModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, OrganizationModel $organizationModel): bool
    {
        return false;
    }
}
