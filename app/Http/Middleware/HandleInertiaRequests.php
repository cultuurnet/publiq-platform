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
                'VITE_PUSHER_APP_KEY' => env('VITE_PUSHER_APP_KEY'),
                'VITE_PUSHER_HOST' => env('VITE_PUSHER_HOST'),
                'VITE_PUSHER_PORT' => env('VITE_PUSHER_PORT'),
                'VITE_PUSHER_SCHEME' => env('VITE_PUSHER_SCHEME'),
                'VITE_PUSHER_APP_CLUSTER' => env('VITE_PUSHER_APP_CLUSTER'),
                'VITE_APP_ENV' => env('VITE_APP_ENV'),
                'VITE_SENTRY_DSN' => env('VITE_SENTRY_DSN'),
                'VITE_SENTRY_ENABLED' => env('VITE_SENTRY_ENABLED'),
                'VITE_UITID_WIDGET_URL' => env('VITE_UITID_WIDGET_URL'),
                'VITE_UITPAS_INTEGRATION_TYPE_ENABLED' => env('VITE_UITPAS_INTEGRATION_TYPE_ENABLED'),
            ],
            'widgetConfig' => [
                'profileUrl' => config('uitidwidget.profileUrl'),
                'registerUrl' => config('uitidwidget.registerUrl'),
                'auth0Domain' => config('uitidwidget.auth0Domain'),
            ],
        ]);
    }
}
