<?php

declare(strict_types=1);

namespace App\Router;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class TranslatedRoute
{
    public const DEFAULT_LANGUAGE = 'nl';

    private static function createTranslatedRouteName(string $routeName, string $language): string
    {
        return implode('.', [$language, $routeName]);
    }

    public static function getTranslatedRouteName(Request $request, string $routeName): string
    {
        $language = $request->headers->get('Accept-Language') ?? self::DEFAULT_LANGUAGE;

        return self::createTranslatedRouteName($routeName, $language);
    }

    public static function get(array $languageToUri, \Closure|array|string|null $action = null, ?string $routeName = null): void
    {
        foreach ($languageToUri as $language => $uri) {
            $route = Route::get($uri, $action);

            if ($routeName !== null) {
                $route->name(self::createTranslatedRouteName($routeName, $language));
            }
        }
    }
}
