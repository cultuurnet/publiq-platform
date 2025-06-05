<?php

declare(strict_types=1);

namespace App\Uitpas;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\TokenStrategy\ClientCredentials;
use App\Uitpas\Listeners\GiveUitpasPermissionsToTestOrganizer;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class UitpasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UitpasApiInterface::class, function () {
            return new UitpasApi(
                $this->app->get(KeycloakGuzzleClient::class),
                new Client([RequestOptions::HTTP_ERRORS => false]),
                new ClientCredentials(
                    $this->app->get(LoggerInterface::class),
                ),
                $this->app->get(LoggerInterface::class),
                (string)config(UitpasConfig::TEST_API_ENDPOINT->value),
                (string)config(UitpasConfig::PROD_API_ENDPOINT->value),
            );
        });

        if (!config(UitpasConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
            return;
        }

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        Event::listen(IntegrationCreated::class, [GiveUitpasPermissionsToTestOrganizer::class, 'handle']);
        Event::listen(IntegrationUpdated::class, [GiveUitpasPermissionsToTestOrganizer::class, 'handle']);
    }
}
