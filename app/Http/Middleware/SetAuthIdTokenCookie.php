<?php

namespace App\Http\Middleware;

use Auth0\SDK\Auth0;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetAuthIdTokenCookie
{
    public const COOKIE = 'auth.token.idToken';

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        if ($auth0->getUser() !== null && $auth0->getIdToken() !== null) {
            Cookie::queue(Cookie::make(
                name: self::COOKIE,
                value: $auth0->getIdToken(),
                minutes: 3600 * 60 * 24,
                httpOnly: false
            ));
        } else {
            Cookie::expire(self::COOKIE);
        }

        return $next($request);
    }
}
