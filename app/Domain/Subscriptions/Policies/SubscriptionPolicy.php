<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Subscriptions\Models\SubscriptionModel;

final class SubscriptionPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, SubscriptionModel $subscriptionModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return false;
    }

    public function update(UserModel $userModel, SubscriptionModel $subscriptionModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, SubscriptionModel $subscriptionModel): bool
    {
        return false;
    }

    public function restore(UserModel $userModel, SubscriptionModel $subscriptionModel): bool
    {
        return false;
    }

    public function replicate(UserModel $userModel, SubscriptionModel $subscriptionModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, SubscriptionModel $subscriptionModel): bool
    {
        return false;
    }
}
