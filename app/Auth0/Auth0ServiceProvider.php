<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Auth0\Repositories\Auth0ClientRepository;
use App\Auth0\Repositories\Auth0ManagementUserRepository;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

final class Auth0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth0ClientRepository::class, function () {
            return $this->app->get(EloquentAuth0ClientRepository::class);
        });

        $this->app->singleton(Auth0ManagementUserRepository::class, function () {
            return new Auth0ManagementUserRepository(
                new SdkConfiguration(
                    strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                    domain: config('auth0.managementDomain'),
                    clientId: config('auth0.clientId'),
                    clientSecret: config('auth0.clientSecret'),
                    audience: config('auth0.audience'),
                )
            );
        });

        $this->app->singleton(Auth0ClusterSDK::class, function () {
            // Filter out tenants with missing config (consider them disabled)
            $tenantsConfig = array_filter(
                config('auth0.tenants'),
                static fn (array $tenantConfig) => $tenantConfig['domain'] !== '' &&
                    $tenantConfig['clientId'] !== '' &&
                    $tenantConfig['clientSecret'] !== ''
            );

            // Create Auth0Tenant objects based on the tenants config keys
            $tenants = array_map(
                static fn (string|int $tenant) => Auth0Tenant::from((string)$tenant),
                array_keys($tenantsConfig)
            );

            // Create a cluster with a tenant SDK per (enabled) tenant config
            return new Auth0ClusterSDK(
                ...array_map(
                    static fn (array $tenantConfig, Auth0Tenant $tenant) => new Auth0TenantSDK(
                        $tenant,
                        new SdkConfiguration(
                            strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                            domain: $tenantConfig['domain'],
                            clientId: $tenantConfig['clientId'],
                            clientSecret: $tenantConfig['clientSecret'],
                            audience: [$tenantConfig['audience']],
                        )
                    ),
                    $tenantsConfig,
                    $tenants
                )
            );
        });

        $this->app->singleton(CachedAuth0ClientGrants::class, function () {
            return new CachedAuth0ClientGrants(
                App::get(Auth0ClusterSDK::class)
            );
        });
    }
}
