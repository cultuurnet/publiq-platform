<?php

declare(strict_types=1);

namespace App\Domain\Activity\Policies;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Activitylog\Models\Activity;

final class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, Activity $activity): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return false;
    }

    public function update(UserModel $userModel, Activity $activity): bool
    {
        return false;
    }

    public function delete(UserModel $userModel, Activity $activity): bool
    {
        return false;
    }

    public function restore(UserModel $userModel, Activity $activity): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, Activity $activity): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, Activity $activity): bool
    {
        return false;
    }
}
