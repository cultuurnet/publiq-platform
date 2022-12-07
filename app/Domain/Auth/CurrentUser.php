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

    private function user(): UserModel
    {
        /** @var UserModel $user */
        $user = $this->auth::user();
        return $user;
    }
}
