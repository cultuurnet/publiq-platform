<?php

namespace App\Policies;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Models\ContactModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(UserModel $userModel)
    {
        return true;
    }

    public function view(UserModel $userModel, ContactModel $contactModel)
    {
        return true;
    }

    public function create(UserModel $userModel)
    {
        return true;
    }

    public function update(UserModel $userModel, ContactModel $contactModel)
    {
        return true;
    }

    public function delete(UserModel $userModel, ContactModel $contactModel)
    {
        return true;
    }

    public function restore(UserModel $userModel, ContactModel $contactModel)
    {
        return false;
    }

    public function forceDelete(UserModel $userModel, ContactModel $contactModel)
    {
        return true;
    }
}
