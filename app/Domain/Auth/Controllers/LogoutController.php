<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\SDK\Contract\Auth0Interface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController
{
    public function __construct(
        private readonly Auth0Interface $auth0
    ) {
    }

    private function logout(): void
    {
        if (Auth::check()) {
            $this->auth0->logout();
            Auth::guard(config('nova.guard'))->logout();
        }
    }

    private function getLogoutLink(): string
    {
        return $this->auth0->authentication()->getLogoutLink(config('app.url'));
    }

    public function adminLogout(): JsonResponse
    {
        $this->logout();
        $logoutLink = $this->getLogoutLink();

        return new JsonResponse([
            'redirect' => $logoutLink,
        ]);
    }

    public function inertiaLogout(): Response
    {
        $this->logout();
        $logoutLink = $this->getLogoutLink();

        return Inertia::location($logoutLink);
    }
}
