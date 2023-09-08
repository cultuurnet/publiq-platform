<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\SDK\Auth0;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class Logout
{
    public function getLogoutLink()
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        $isLoggedIn = Auth::check();

        if ($isLoggedIn) {
            // deletes logged in state in auth0 sdk
            $auth0->logout();

            // logout in custom auth guard
            Auth::guard(config('nova.guard'))->logout();
        }

        $redirectTo = config('app.url');

        return $auth0->authentication()->getLogoutLink($redirectTo);
    }
    public function admin(): JsonResponse {
        $logoutLink = $this->getLogoutLink();

        return new JsonResponse([
            'redirect' => $logoutLink,
        ]);
    }

    public function inertia() {
        $logoutLink = $this->getLogoutLink();

        return new RedirectResponse($logoutLink);
    }
}
