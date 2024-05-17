<?php

declare(strict_types=1);

namespace App\Keycloak\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Keycloak\Models\KeycloakClientModel;

final class KeycloakClientPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, KeycloakClientModel $keycloakClientModel): bool
    {
        return false;
    }

    public function create(UserModel $userModel): bool
    {
        return false;
    }

    public function update(UserModel $userModel, KeycloakClientModel $keycloakClientModel): bool
    {
        return false;
    }

    public function delete(UserModel $userModel, KeycloakClientModel $keycloakClientModel): bool
    {
        return false;
    }

    public function restore(UserModel $userModel, KeycloakClientModel $keycloakClientModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, KeycloakClientModel $keycloakClientModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, KeycloakClientModel $keycloakClientModel): bool
    {
        return false;
    }
}
