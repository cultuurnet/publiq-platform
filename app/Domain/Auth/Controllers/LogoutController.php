<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController
{
    public function __construct(private readonly AuthenticationStrategy $authenticationStrategy)
    {
    }

    private function getLogoutLink(): string
    {
        $this->authenticationStrategy->logout();

        if (Auth::check()) {
            $this->authenticationStrategy->logout();
            Auth::guard(config('nova.guard'))->logout();
        }

        return $this->authenticationStrategy->getLogoutLink(config('app.url'));
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
