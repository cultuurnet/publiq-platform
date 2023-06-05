<?php

declare(strict_types=1);

namespace App\Router;

use Illuminate\Support\Facades\Route;

final class TranslatedRoute
{
    public static function getTranslatedRouteName(string $routeName, string $language): string
    {
        return implode('.', [$language, $routeName]);
    }

    public static function get(array $languageToUri, \Closure|array|string|null $action = null, ?string $routeName = null): void
    {
        foreach ($languageToUri as $language => $uri) {
            $route = Route::get($uri, $action);

            if ($routeName !== null) {
                $route->name(self::getTranslatedRouteName($routeName, $language));
            }
        }
    }
}
