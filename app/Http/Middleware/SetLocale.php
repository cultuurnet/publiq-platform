<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Router\TranslatedRoute;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

final class SetLocale
{
    private function getLocaleFromUrl(Request $request): ?string
    {
        $parts = explode('/', $request->path())[0];

        return in_array($parts, TranslatedRoute::SUPPORTED_LANGUAGES) ? $parts : null;
    }

    private function setLocaleCookie(string $locale): void
    {
        Cookie::queue(Cookie::make(name: 'locale', value: $locale, minutes: 3600 * 60 * 24));
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $locale = Cookie::get('locale');

        if ($locale === null || $request->query('setLocale') === 'true') {
            $localeFromUrl = $this->getLocaleFromUrl($request);
            $locale = $localeFromUrl ?? TranslatedRoute::DEFAULT_LANGUAGE;
            $this->setLocaleCookie($locale);
        }

        if (gettype($locale) === 'array') {
            App::setLocale($locale[0]);
        }

        if (gettype($locale) === 'string') {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
