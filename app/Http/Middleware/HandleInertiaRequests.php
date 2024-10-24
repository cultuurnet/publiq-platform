<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'auth' => [
                'authenticated' => Auth::check(),
            ],
            'config' => [
                'env' => config('app.env'),
                'sentry' => [
                    'dsn' => config('sentry.dsn'),
                    'enabled' => config('app.sentry.enabled'),
                ],
                'keycloak' => [
                    'enabled' => config('keycloak.enabled'),
                    'creationEnabled' => config('keycloak.creationEnabled'),
                ],
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
