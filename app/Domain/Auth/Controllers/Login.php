<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\SDK\Auth0;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class Login
{
    /** @var array<string> */
    private array $loginParams;

    /** @param array<string> $loginParams */
    public function __construct(array $loginParams)
    {
        $this->loginParams = $loginParams;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        $auth0->clear();

        if (Auth::check()) {
            return redirect()->intended(config('auth0.routes.home', '/'));
        }

        return redirect()->away($auth0->login(null, $this->loginParams));
    }
}
