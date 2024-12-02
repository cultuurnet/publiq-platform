<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\SDK\Auth0;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final readonly class LoginController
{
    /** @param array<string> $loginParams */
    public function __construct(private array $loginParams)
    {
    }

    private function getAuth0LoginUrl(): string
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        $auth0->clear();

        return $auth0->login(null, $this->loginParams);
    }

    public function adminLogin(): RedirectResponse
    {
        if (Auth::check()) {
            return Redirect::intended();
        }

        return Redirect::to($this->getAuth0LoginUrl());
    }

    public function inertiaLogin(): Response
    {
        if (Auth::check()) {
            return Redirect::intended();
        }

        return Inertia::location($this->getAuth0LoginUrl());
    }
}
