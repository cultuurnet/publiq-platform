<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

final class RedirectRoot
{
    public function handle(Request $request, Closure $next): mixed
    {
        $language = App::currentLocale();

        if ($request->path() === '/') {
            return redirect('/' . $language);
        }

        return $next($request);
    }
}
