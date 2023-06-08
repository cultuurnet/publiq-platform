<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

final class SetLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->headers->get('Accept-Language');

        if ($locale !== null) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
