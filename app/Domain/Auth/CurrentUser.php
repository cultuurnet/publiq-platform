<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Support\Facades\Auth;

final class CurrentUser
{
    public function __construct(private readonly Auth $auth)
    {
    }

    public function id(): string
    {
        return $this->user()->id;
    }

    public function email(): string
    {
        return $this->user()->email;
    }

    public function name(): string
    {
        return $this->user()->name;
    }

    public function firstName(): string
    {
        return $this->user()->first_name;
    }

    public function lastName(): string
    {
        return $this->user()->last_name;
    }

    private function user(): UserModel
    {
        /** @var UserModel $user */
        $user = $this->auth::user();
        return $user;
    }
}
