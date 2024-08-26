<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'layouts/default';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'config' => [
                'env' => config('app.env'),
                'sentry' => [
                    'dsn' => config('sentry.dsn'),
                    'enabled' => config('app.sentry.enabled'),
                ],
                'uitpas' => [
                    'enabled' => config('uitpas.enabled'),
                ],
                'keycloak' => [
                    'enabled' => config('keycloak.enabled'),
                    'testClientEnabled' => config('keycloak.testClientEnabled'),
                ]
            ],
            'widgetConfig' => [
                'url' => config('uitidwidget.url'),
                'profileUrl' => config('uitidwidget.profileUrl'),
                'registerUrl' => config('uitidwidget.registerUrl'),
                'auth0Domain' => config('uitidwidget.auth0Domain'),
            ],
        ]);
    }
}
