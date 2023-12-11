<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\SDK\Auth0;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController
{
    private function getLogoutLink(): string
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        if (Auth::check()) {
            $auth0->logout();
            Auth::guard(config('nova.guard'))->logout();
        }

        return $auth0->authentication()->getLogoutLink(config('app.url'));
    }

    public function adminLogout(): JsonResponse
    {
        $logoutLink = $this->getLogoutLink();

        return new JsonResponse([
            'redirect' => $logoutLink,
        ]);
    }

    public function inertiaLogout(): Response
    {
        $logoutLink = $this->getLogoutLink();

        return Inertia::location($logoutLink);
    }
}
