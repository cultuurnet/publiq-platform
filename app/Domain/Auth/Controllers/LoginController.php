<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final readonly class LoginController
{
    private AuthenticationStrategy $authenticationStrategy;

    /** @param array<string> $loginParams */
    public function __construct(private array $loginParams)
    {
        $this->authenticationStrategy = App::get(AuthenticationStrategy::class);
    }

    public function adminLogin(): RedirectResponse
    {
        if (Auth::check()) {
            return Redirect::intended(config('auth0.routes.home', '/'));
        }

        return Redirect::to($this->authenticationStrategy->getLoginUrl($this->loginParams));
    }

    public function inertiaLogin(): Response
    {
        if (Auth::check()) {
            return Redirect::intended(config('auth0.routes.home', '/'));
        }

        return Inertia::location($this->authenticationStrategy->getLoginUrl($this->loginParams));
    }
}
