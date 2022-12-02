<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth0\Auth0Client;
use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use Auth0\SDK\Configuration\SdkConfiguration;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InsightlyClient::class, function () {
            return new InsightlyClient(
                new Client(
                    [
                        'base_uri' => config('insightly.host'),
                        'http_errors' => false,
                    ]
                ),
                config('insightly.api_key'),
                new Pipelines(config('insightly.pipelines'))
            );
        });

        $this->app->singleton(Auth0Client::class, function () {
            $configuration = new SdkConfiguration(
                domain: config('auth0.management.domain'),
                clientId: config('auth0.management.clientId'),
                clientSecret: config('auth0.management.clientSecret'),
                audience: [config('auth0.management.audience')],
                cookieSecret: config('auth0.management.cookieSecret'),
            );

            return new Auth0Client($configuration);
        });
    }

    public function boot(): void
    {
    }
}
