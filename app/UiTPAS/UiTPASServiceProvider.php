<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\GetIntegrationOrganizersWithTestOrganizer;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Notifications\MessageBuilder;
use App\Notifications\Slack\SlackNotifier;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Nova\Actions\ActivateUdbOrganizer;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\Listeners\AddUiTPASPermissionsToOrganizerForIntegration;
use App\UiTPAS\Listeners\NotifyUdbOrganizerRequested;
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
            );
        });

        $this->app->singleton(AddUiTPASPermissionsToOrganizerForIntegration::class, function () {
            return new AddUiTPASPermissionsToOrganizerForIntegration(
                $this->app->get(IntegrationRepository::class),
                $this->app->get(KeycloakClientRepository::class),
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

        $this->app->singleton(NotifyUdbOrganizerRequested::class, function () {
            return new NotifyUdbOrganizerRequested(
                $this->app->get(UdbOrganizerRepository::class),
                $this->app->get(IntegrationRepository::class),
                new SlackNotifier(
                    config('slack.botToken'),
                    config('slack.channels.uitpas_integraties'),
                    config('slack.baseUri')
                ),
                $this->app->get(MessageBuilder::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        if (!config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
            return;
        }

        Event::listen(ClientCreated::class, [AddUiTPASPermissionsToOrganizerForIntegration::class, 'handle']);
        Event::listen(UdbOrganizerCreated::class, [NotifyUdbOrganizerRequested::class, 'handle']);
    }
}
