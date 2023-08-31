<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\UserModel;
use Auth0\SDK\Auth0;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

final class Callback
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        if ($auth0->exchange()) {
            $user = $auth0->getUser();

            if ($user !== null) {
                Cookie::queue('auth.token.idToken', $auth0->getIdToken(), 3600 * 60 * 24);

                Auth::login(UserModel::fromSession($user));
            } else {
                Cookie::queue('auth.token.idToken', '', 0);
            }
        }

        return redirect()->intended('/');
    }
}
