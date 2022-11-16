<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

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

        return new JsonResponse([
            'redirect' => config('auth0.routes.home', '/'),
        ]);
    }
}
