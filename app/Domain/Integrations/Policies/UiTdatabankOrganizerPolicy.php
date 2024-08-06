<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Integrations\Models\UdbOrganizerModel;

final class UiTdatabankOrganizerPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, UdbOrganizerModel $organizerModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return false;
    }

    public function update(UserModel $userModel, UdbOrganizerModel $organizerModel): bool
    {
        return false;
    }

    public function delete(UserModel $userModel, UdbOrganizerModel $organizerModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, UdbOrganizerModel $organizerModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, UdbOrganizerModel $organizerModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, UdbOrganizerModel $organizerModel): bool
    {
        return false;
    }
}
