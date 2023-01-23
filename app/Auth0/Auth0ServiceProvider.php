<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Auth0\Listeners\CreateClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class Auth0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth0ClientRepository::class, function () {
            return $this->app->get(EloquentAuth0ClientRepository::class);
        });

        $this->app->singleton(Auth0ClusterSDK::class, function () {
            // Filter out tenants with missing config (consider them disabled)
            $tenantsConfig = array_filter(
                config('auth0.tenants'),
                static fn (array $tenantConfig) =>
                    $tenantConfig['domain'] !== '' &&
                    $tenantConfig['clientId'] !== '' &&
                    $tenantConfig['clientSecret'] !== ''
            );

            // Create Auth0Tenant objects based on the tenants config keys
            $tenants = array_map(
                static fn (string|int $tenant) => Auth0Tenant::from((string) $tenant),
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

        if (config('auth0.enabled')) {
            // May always be registered even if there are no configured tenants, because in that case the cluster SDK will
            // just not have any tenant SDKs to loop over and so it simply won't do anything. But it won't crash either.
            Event::listen(IntegrationCreated::class, [CreateClients::class, 'handle']);
        }
    }
}
