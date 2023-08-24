<?php

declare(strict_types=1);

namespace App\Auth0\Policies;

use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Auth\Models\UserModel;

final class Auth0ClientPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, Auth0ClientModel $auth0ClientModel): bool
    {
        return false;
    }

    public function create(UserModel $userModel): bool
    {
        return false;
    }

    public function update(UserModel $userModel, Auth0ClientModel $auth0ClientModel): bool
    {
        return false;
    }

    public function delete(UserModel $userModel, Auth0ClientModel $auth0ClientModel): bool
    {
        return false;
    }

    public function restore(UserModel $userModel, Auth0ClientModel $auth0ClientModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, Auth0ClientModel $auth0ClientModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, Auth0ClientModel $auth0ClientModel): bool
    {
        return false;
    }
}
