<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Auth0Tenant;
use App\Auth0\Auth0TenantSDK;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Support\ServiceProvider;

final class Auth0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
                    array_filter(
                        config('auth0.tenants'),
                        static fn (array $tenantConfig) =>
                            $tenantConfig['domain'] !== '' &&
                            $tenantConfig['clientId'] !== '' &&
                            $tenantConfig['clientSecret'] !== ''
                    )
                )
            );
        });
    }
}
