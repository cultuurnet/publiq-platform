<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\TokenStrategy\ClientCredentials;
use App\UiTPAS\Listeners\GiveUitpasPermissionsToTestOrganizer;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class UiTPASServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UiTPASApiInterface::class, function () {
            return new UiTPASApi(
                $this->app->get(KeycloakGuzzleClient::class),
                new Client([RequestOptions::HTTP_ERRORS => false]),
                new ClientCredentials(
                    $this->app->get(LoggerInterface::class),
                ),
                $this->app->get(LoggerInterface::class),
                (string)config(UiTPASConfig::TEST_API_ENDPOINT->value),
                (string)config(UiTPASConfig::PROD_API_ENDPOINT->value),
            );
        });

        if (!config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
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
