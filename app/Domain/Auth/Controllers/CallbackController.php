<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use App\Domain\Auth\Models\UserModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final readonly class CallbackController
{
    public function __construct(private AuthenticationStrategy $authenticationStrategy)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        if ($this->authenticationStrategy->exchange($request)) {
            $user = $this->authenticationStrategy->getUser();
            Session::put('id_token', $this->authenticationStrategy->getIdToken());

            if ($user !== null) {
                Auth::login(UserModel::fromSession($user));
            }
        }

        return redirect()->intended('/');
    }
}
