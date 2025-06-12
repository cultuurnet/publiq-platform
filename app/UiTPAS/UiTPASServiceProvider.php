<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\GetIntegrationOrganizersWithTestOrganizer;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\Listeners\AddUiTPASPermissionsToOrganizerForIntegration;
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
                new Client([RequestOptions::HTTP_ERRORS => false]),
                new ClientCredentials(
                    new Client([RequestOptions::HTTP_ERRORS => false]),
                    $this->app->get(LoggerInterface::class),
                ),
                $this->app->get(LoggerInterface::class),
                (string)config(UiTPASConfig::TEST_API_ENDPOINT->value),
                (string)config(UiTPASConfig::PROD_API_ENDPOINT->value),
                (bool)config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)
            );
        });

        $this->app->singleton(AddUiTPASPermissionsToOrganizerForIntegration::class, function () {
            return new AddUiTPASPermissionsToOrganizerForIntegration(
                $this->app->get(IntegrationRepository::class),
                $this->app->get(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdTestContext()
            );
        });

        $this->app->singleton(GetIntegrationOrganizersWithTestOrganizer::class, function () {
            return new GetIntegrationOrganizersWithTestOrganizer(
                $this->app->get(SearchService::class),
                $this->app->get(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdTestContext(),
                ClientCredentialsContextFactory::getUitIdProdContext(),
            );
        });


        if (!config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
            return;
        }

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        Event::listen(IntegrationCreated::class, [AddUiTPASPermissionsToOrganizerForIntegration::class, 'handle']);
    }
}
