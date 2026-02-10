<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Domain\Auth\Repositories\UserRepository;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\ProjectAanvraag\Listeners\SyncWidget;
use App\UiTiDv1\Events\ConsumerCreated;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class ProjectAanvraagServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProjectAanvraagClient::class, function () {
            $httpClient = new Client(
                [
                  RequestOptions::HTTP_ERRORS => false,
                  RequestOptions::TIMEOUT => config('project_aanvraag.timeout'),
              ]
            );

            return new ProjectAanvraagClient(
                $this->app->get(LoggerInterface::class),
                $httpClient
            );
        });

        $this->app->singleton(SyncWidget::class, function () {
            $groups = config('uitidv1.environments.prod.groups.widgets');
            $groups = explode(',', str_replace(' ', '', $groups));

            return new SyncWidget(
                $this->app->get(ProjectAanvraagClient::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(ContactRepository::class),
                $this->app->get(UiTiDv1ConsumerRepository::class),
                $this->app->get(KeycloakClientRepository::class),
                (int) end($groups),
                $this->app->get(UserRepository::class),
                $this->app->get(LoggerInterface::class)
            );
        });

        if (config('project_aanvraag.create_widget', false)) {
            Event::listen(IntegrationCreated::class, [SyncWidget::class, 'handleIntegrationCreated']);
            Event::listen(ContactCreated::class, [SyncWidget::class, 'handleContactCreated']);
            Event::listen(ConsumerCreated::class, [SyncWidget::class, 'handleConsumerCreated']);

            Event::listen(IntegrationActivated::class, [SyncWidget::class, 'handleIntegrationActivated']);
            Event::listen(IntegrationBlocked::class, [SyncWidget::class, 'handleIntegrationBlocked']);
            Event::listen(IntegrationUnblocked::class, [SyncWidget::class, 'handleIntegrationUnblocked']);
            Event::listen(IntegrationDeleted::class, [SyncWidget::class, 'handleIntegrationDeleted']);

            Event::listen(IntegrationUpdated::class, [SyncWidget::class, 'handleIntegrationUpdated']);
        }
    }
}
