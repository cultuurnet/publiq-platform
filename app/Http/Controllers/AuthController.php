<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Users\Models\UserModel;
use Illuminate\Support\Facades\Auth;

abstract class AuthController extends Controller
{
    public function __construct(
        private readonly Auth $auth
    ) {
    }

    public function getUser(): UserModel
    {
        /** @var UserModel $user */
        $user = $this->auth::user();

        return $user;
    }
}
