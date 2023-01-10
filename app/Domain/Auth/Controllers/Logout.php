<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\Laravel\Auth0;
use Auth0\Laravel\Contract\Auth\Guard;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class Logout
{
    public function __invoke(Request $request): JsonResponse
    {
        $auth = auth();

        /**
         * @var Factory $auth
         */
        $guard = $auth->guard('auth0');

        /**
         * @var Guard $guard
         */
        if ($guard->check()) {
            $request->session()->invalidate();

            $guard->logout();

            Auth::guard(config('nova.guard'))->logout();
        }

        $auth0LogoutLink = app(Auth0::class)
            ->getSdk()
            ->authentication()
            ->getLogoutLink(config('app.url'));

        return new JsonResponse([
            'redirect' => $auth0LogoutLink,
        ]);
    }
}
