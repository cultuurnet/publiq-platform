<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Events\IntegrationUrlCreated;
use App\Domain\Integrations\Events\IntegrationUrlDeleted;
use App\Domain\Integrations\Events\IntegrationUrlUpdated;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\Client\KeycloakHttpClient;
use App\Keycloak\Events\MissingClientsDetected;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Listeners\DisableClients;
use App\Keycloak\Listeners\UpdateClients;
use App\Keycloak\Repositories\EloquentKeycloakClientRepository;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiClient::class, function () {
            return new KeycloakApiClient(
                new KeycloakHttpClient(
                    new Client([RequestOptions::HTTP_ERRORS => false]),
                    $this->app->get(Config::class),
                    new ClientCredentials(
                        $this->app->get(Config::class),
                        $this->app->get(LoggerInterface::class)
                    )
                ),
                $this->app->get(ScopeConfig::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(Config::class, function () {
            return new Config(
                config('keycloak.enabled'),
                config('keycloak.base_url'),
                config('keycloak.client_id'),
                config('keycloak.client_secret'),
                RealmCollection::getRealms(),
            );
        });

        $this->app->singleton(ScopeConfig::class, function () {
            return new ScopeConfig(
                Uuid::fromString(config('keycloak.scope.search_api_id')),
                Uuid::fromString(config('keycloak.scope.entry_api_id')),
                Uuid::fromString(config('keycloak.scope.widgets_id')),
                Uuid::fromString(config('keycloak.scope.uitpas_id')),
            );
        });

        $this->app->singleton(KeycloakClientRepository::class, function () {
            return $this->app->get(EloquentKeycloakClientRepository::class);
        });

        $this->app->singleton(CachedKeycloakClientStatus::class, function () {
            return new CachedKeycloakClientStatus(
                App::get(ApiClient::class),
                App::get(LoggerInterface::class),
            );
        });

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        if (!$this->app->get(Config::class)->isEnabled) {
            return;
        }

        Event::listen(IntegrationCreated::class, [CreateClients::class, 'handleCreateClients']);
        Event::listen(IntegrationUpdated::class, [UpdateClients::class, 'handle']);
        Event::listen(IntegrationBlocked::class, [DisableClients::class, 'handle']);
        Event::listen(MissingClientsDetected::class, [CreateClients::class, 'handleCreatingMissingClients']);

        Event::listen(IntegrationUrlCreated::class, [UpdateClients::class, 'handle']);
        Event::listen(IntegrationUrlUpdated::class, [UpdateClients::class, 'handle']);
        Event::listen(IntegrationUrlDeleted::class, [UpdateClients::class, 'handle']);
    }
}
