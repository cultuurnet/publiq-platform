<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Auth0Tenant;
use App\Auth0\Auth0TenantSDK;
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

        $this->app->singleton(Auth0ClusterSDK::class, function () {
            return new Auth0ClusterSDK(
                ...array_map(
                    static fn (array $tenantConfig, string $tenant) => new Auth0TenantSDK(
                        Auth0Tenant::from($tenant),
                        new SdkConfiguration(
                            domain: $tenantConfig['domain'],
                            clientId: $tenantConfig['clientId'],
                            clientSecret: $tenantConfig['clientSecret'],
                            audience: [$tenantConfig['audience']],
                        )
                    ),
                    config('auth0.tenants')
                )
            );
        });
    }

    public function boot(): void
    {
    }
}
