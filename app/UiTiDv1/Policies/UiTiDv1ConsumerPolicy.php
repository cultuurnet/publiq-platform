<?php

declare(strict_types=1);

namespace App\UiTiDv1\Policies;

use App\Domain\Auth\Models\UserModel;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;

final class UiTiDv1ConsumerPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, UiTiDv1ConsumerModel $uiTiDv1ConsumerModel): bool
    {
        return false;
    }

    public function create(UserModel $userModel): bool
    {
        return false;
    }

    public function update(UserModel $userModel, UiTiDv1ConsumerModel $uiTiDv1ConsumerModel): bool
    {
        return false;
    }

    public function delete(UserModel $userModel, UiTiDv1ConsumerModel $uiTiDv1ConsumerModel): bool
    {
        return false;
    }

    public function restore(UserModel $userModel, UiTiDv1ConsumerModel $uiTiDv1ConsumerModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, UiTiDv1ConsumerModel $uiTiDv1ConsumerModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, UiTiDv1ConsumerModel $uiTiDv1ConsumerModel): bool
    {
        return false;
    }
}
