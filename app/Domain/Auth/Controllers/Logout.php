<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\SDK\Auth0;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class Logout
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        if (Auth::check()) {
            $auth0->logout();
            Auth::guard(config('nova.guard'))->logout();
        }

        $auth0LogoutLink = $auth0->authentication()->getLogoutLink(config('app.url'));

        return new JsonResponse([
            'redirect' => $auth0LogoutLink,
        ]);
    }
}
