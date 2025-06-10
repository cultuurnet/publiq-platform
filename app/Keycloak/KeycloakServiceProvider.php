<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Events\IntegrationUrlCreated;
use App\Domain\Integrations\Events\IntegrationUrlDeleted;
use App\Domain\Integrations\Events\IntegrationUrlUpdated;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\Events\MissingClientsDetected;
use App\Keycloak\Listeners\BlockClients;
use App\Keycloak\Listeners\CreateClients;
use App\Keycloak\Listeners\UnblockClients;
use App\Keycloak\Listeners\UpdateClients;
use App\Keycloak\Repositories\EloquentKeycloakClientRepository;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Repositories\KeycloakUserRepository;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KeycloakGuzzleClient::class, function () {
            return new KeycloakGuzzleClient(
                new Client([RequestOptions::HTTP_ERRORS => false]),
                new ClientCredentials(
                    new Client([RequestOptions::HTTP_ERRORS => false]),
                    $this->app->get(LoggerInterface::class),
                ),
            );
        });

        $this->app->singleton(ApiClient::class, function () {
            return new KeycloakApiClient(
                $this->app->get(KeycloakGuzzleClient::class),
                $this->app->get(Realms::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(Realms::class, function () {
            return Realms::build();
        });

        $this->app->singleton(KeycloakClientRepository::class, function () {
            return new EloquentKeycloakClientRepository($this->app->get(Realms::class));
        });

        $this->app->singleton(CachedKeycloakClientStatus::class, function () {
            return new CachedKeycloakClientStatus(
                $this->app->get(ApiClient::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(KeycloakUserRepository::class, function () {
            return new KeycloakUserRepository(
                $this->app->get(KeycloakGuzzleClient::class),
                $this->app->get(Realms::class)->getRealmByEnvironment(Environment::Production),
            );
        });

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        if (!config(KeycloakConfig::KEYCLOAK_CREATION_ENABLED)) {
            return;
        }

        Event::listen(IntegrationCreated::class, [CreateClients::class, 'handleCreateClients']);
        Event::listen(IntegrationUpdated::class, [UpdateClients::class, 'handle']);
        Event::listen(IntegrationBlocked::class, [BlockClients::class, 'handle']);
        Event::listen(IntegrationUnblocked::class, [UnblockClients::class, 'handle']);
        Event::listen(IntegrationDeleted::class, [BlockClients::class, 'handle']);

        Event::listen(MissingClientsDetected::class, [CreateClients::class, 'handleCreatingMissingClients']);

        Event::listen(IntegrationUrlCreated::class, [UpdateClients::class, 'handle']);
        Event::listen(IntegrationUrlUpdated::class, [UpdateClients::class, 'handle']);
        Event::listen(IntegrationUrlDeleted::class, [UpdateClients::class, 'handle']);
    }
}
