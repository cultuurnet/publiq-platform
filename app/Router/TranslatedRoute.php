<?php

declare(strict_types=1);

namespace App\Router;

use Illuminate\Support\Facades\Route;

final class TranslatedRoute
{
    public static function get(array $uris, \Closure|array|string|null $action = null, ?string $routeName = null): void
    {
        foreach ($uris as $uri) {
            $route = Route::get($uri, $action);

            if ($routeName !== null) {
                $route->name($routeName);
            }
        }
    }
}
