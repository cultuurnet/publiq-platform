<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Models\ContactModel;

final class ContactPolicy
{
    public function viewAny(UserModel $userModel): bool
    {
        return true;
    }

    public function view(UserModel $userModel, ContactModel $contactModel): bool
    {
        return true;
    }

    public function create(UserModel $userModel): bool
    {
        return true;
    }

    public function update(UserModel $userModel, ContactModel $contactModel): bool
    {
        return true;
    }

    public function delete(UserModel $userModel, ContactModel $contactModel): bool
    {
        return true;
    }

    public function restore(UserModel $userModel, ContactModel $contactModel): bool
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, ContactModel $contactModel): bool
    {
        return true;
    }
}
