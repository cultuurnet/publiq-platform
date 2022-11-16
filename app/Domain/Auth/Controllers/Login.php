<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use Auth0\Laravel\Auth0;
use Auth0\Laravel\Contract\Auth\Guard;
use Auth0\SDK\Contract\Auth0Interface;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class Login implements \Auth0\Laravel\Contract\Http\Controller\Stateful\Login
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
        /** @var Factory $auth */
        $auth = auth();

        /** @var Guard $guard */
        $guard = $auth->guard('auth0');

        if ($guard->check()) {
            return redirect()->intended(config('auth0.routes.home', '/'));
        }

        /** @var Auth0Interface $sdk */
        $sdk = app(Auth0::class)->getSdk();
        return redirect()->away($sdk->login(null, $this->loginParams));
    }
}
