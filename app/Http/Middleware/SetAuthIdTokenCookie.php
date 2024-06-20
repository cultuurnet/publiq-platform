<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class SetAuthIdTokenCookie
{
    public const COOKIE = 'auth.token.idToken';

    public function handle(Request $request, Closure $next): Response
    {
        /** @var AuthenticationStrategy $authenticationStrategy */
        $authenticationStrategy = app(AuthenticationStrategy::class);

        if ($authenticationStrategy->getUser() !== null && $authenticationStrategy->getIdToken() !== null) {
            Cookie::queue(Cookie::make(
                name: self::COOKIE,
                value: $authenticationStrategy->getIdToken(),
                minutes: 3600 * 60 * 24,
                httpOnly: false,
            ));
        } else {
            Cookie::expire(self::COOKIE);
        }

        return $next($request);
    }
}
