<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Auth0\Jobs\UnblockClient;
use App\Auth0\Jobs\UnblockClientHandler;
use App\Auth0\Jobs\BlockClient;
use App\Auth0\Jobs\BlockClientHandler;
use App\Auth0\Jobs\CreateMissingClients;
use App\Auth0\Jobs\CreateMissingClientsHandler;
use App\Auth0\Listeners\BlockClients;
use App\Auth0\Listeners\CreateClients;
use App\Auth0\Listeners\UnblockClients;
use App\Auth0\Listeners\UpdateClients;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Auth0\Repositories\Auth0ManagementUserRepository;
use App\Auth0\Repositories\Auth0UserRepository;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Events\IntegrationUrlCreated;
use App\Domain\Integrations\Events\IntegrationUrlDeleted;
use App\Domain\Integrations\Events\IntegrationUrlUpdated;
use App\Keycloak\KeycloakConfig;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class Auth0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth0ClientRepository::class, function () {
            return $this->app->get(EloquentAuth0ClientRepository::class);
        });

        $this->app->singleton(Auth0UserRepository::class, function () {
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

        $this->app->singleton(CachedAuth0ClientGrants::class, function () {
            return new CachedAuth0ClientGrants(
                App::get(Auth0ClusterSDK::class)
            );
        });

        if (config(KeycloakConfig::IS_ENABLED)) {
            // By default, the Auth0 integration is enabled. For testing purposes this can be disabled inside the .env file.

            // May always be registered even if there are no configured tenants, because in that case the cluster SDK will
            // just not have any tenant SDKs to loop over and so it simply won't do anything. But it won't crash either.
            Event::listen(IntegrationCreated::class, [CreateClients::class, 'handle']);
            Event::listen(IntegrationUpdated::class, [UpdateClients::class, 'handle']);
            Event::listen(IntegrationBlocked::class, [BlockClients::class, 'handle']);
            Event::listen(IntegrationUnblocked::class, [UnblockClients::class, 'handle']);
            Event::listen(IntegrationDeleted::class, [BlockClients::class, 'handle']);

            Event::listen(IntegrationUrlCreated::class, [UpdateClients::class, 'handle']);
            Event::listen(IntegrationUrlUpdated::class, [UpdateClients::class, 'handle']);
            Event::listen(IntegrationUrlDeleted::class, [UpdateClients::class, 'handle']);

            Event::listen(UnblockClient::class, [UnblockClientHandler::class, 'handle']);
            Event::listen(BlockClient::class, [BlockClientHandler::class, 'handle']);

            Event::listen(CreateMissingClients::class, [CreateMissingClientsHandler::class, 'handle']);
        }
    }
}
