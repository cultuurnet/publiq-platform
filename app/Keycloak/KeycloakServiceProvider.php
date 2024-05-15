<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Client\KeycloakHttpClient;
use App\Keycloak\Repositories\EloquentKeycloakClientRepository;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\ApiClient;
use App\Keycloak\Service\ApiClientInterface;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiClientInterface::class, function () {
            return new ApiClient(
                new KeycloakHttpClient(
                    new Client([RequestOptions::HTTP_ERRORS => false]),
                    $this->app->get(Config::class),
                    new ClientCredentials(
                        $this->app->get(Config::class),
                        $this->app->get(LoggerInterface::class)
                    )
                ),
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
            );
        });

        $this->app->singleton(KeycloakClientRepository::class, function () {
            return $this->app->get(EloquentKeycloakClientRepository::class);
        });

        $this->applyEvents();
    }

    private function applyEvents(): void
    {
        if (!$this->app->get(Config::class)->isEnabled) {
            return;
        }

        Event::listen(IntegrationCreated::class, [CreateClients::class, 'handle']);
    }
}
