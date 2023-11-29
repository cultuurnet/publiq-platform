<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\UserModel;
use Auth0\SDK\Auth0;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class CallbackController
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        if ($auth0->exchange()) {
            $user = $auth0->getUser();
            Session::put('id_token', $auth0->getIdToken());

            if ($user !== null) {
                Auth::login(UserModel::fromSession($user));
            }
        }

        return redirect()->intended('/');
    }
}
