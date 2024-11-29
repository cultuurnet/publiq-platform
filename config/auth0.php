<?php

declare(strict_types=1);

/**
 * Please review available configuration options here:
 * https://github.com/auth0/auth0-PHP#configuration-options.
 */
return [
    // Named routes within your Laravel application that the SDK may call during stateful requests for redirections.
    'routes' => [
        'home'  => env('AUTH0_LOGIN_ROUTE_HOME', '/'),
        'login' => env('AUTH0_LOGIN_ROUTE_LOGIN', 'login'),
    ],
];
