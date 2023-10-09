<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Router\TranslatedRoute;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

final class SetLocale
{
    private function processLocale(string|null $locale): string
    {
        if ($locale === null) {
            return TranslatedRoute::DEFAULT_LANGUAGE;
        }

        $isSingleLocale = !str_contains(';', $locale);

        if ($isSingleLocale && array_search($locale, TranslatedRoute::SUPPORTED_LANGUAGES)) {
            return $locale;
        }

        $firstLocale = explode(';', $locale)[0];

        foreach (TranslatedRoute::SUPPORTED_LANGUAGES as $supported) {
            if (str_starts_with($firstLocale, $supported)) {
                return $supported;
            }
        }

        return TranslatedRoute::DEFAULT_LANGUAGE;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->headers->get('Accept-Language');

        $processed = $this->processLocale($locale);

        App::setLocale($processed);

        return $next($request);
    }
}
