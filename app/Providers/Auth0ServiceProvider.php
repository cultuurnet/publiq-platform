<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Auth0Tenant;
use App\Auth0\Auth0TenantSDK;
use App\Auth0\Listeners\CreateClients;
use App\Domain\Integrations\Events\IntegrationCreated;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class Auth0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth0ClusterSDK::class, function () {
            $tenantsConfig = array_filter(
                config('auth0.tenants'),
                static fn (array $tenantConfig) =>
                    $tenantConfig['domain'] !== '' &&
                    $tenantConfig['clientId'] !== '' &&
                    $tenantConfig['clientSecret'] !== ''
            );

            $tenants = array_map(
                static fn (string|int $tenant) => Auth0Tenant::from((string) $tenant),
                array_keys($tenantsConfig)
            );

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

        Event::listen(IntegrationCreated::class, [CreateClients::class, 'handle']);
    }
}
