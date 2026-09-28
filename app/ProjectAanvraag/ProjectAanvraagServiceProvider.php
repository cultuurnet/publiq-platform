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
use App\Keycloak\Events\ClientsCreated;
use App\ProjectAanvraag\Listeners\SyncWidget;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
            $groupId = (int) config('project_aanvraag.widget_group_id');

            if ($groupId <= 0) {
                throw new RuntimeException(
                    'PROJECT_AANVRAAG_WIDGET_GROUP_ID is missing or invalid, refusing to sync widgets with group id ' . $groupId
                );
            }

            return new SyncWidget(
                $this->app->get(ProjectAanvraagClient::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(ContactRepository::class),
                $groupId,
                $this->app->get(UserRepository::class),
                $this->app->get(LoggerInterface::class)
            );
        });

        if (config('project_aanvraag.create_widget', false)) {
            Event::listen(IntegrationCreated::class, [SyncWidget::class, 'handleIntegrationCreated']);
            Event::listen(ContactCreated::class, [SyncWidget::class, 'handleContactCreated']);
            Event::listen(ClientsCreated::class, [SyncWidget::class, 'handleClientsCreated']);

            Event::listen(IntegrationActivated::class, [SyncWidget::class, 'handleIntegrationActivated']);
            Event::listen(IntegrationBlocked::class, [SyncWidget::class, 'handleIntegrationBlocked']);
            Event::listen(IntegrationUnblocked::class, [SyncWidget::class, 'handleIntegrationUnblocked']);
            Event::listen(IntegrationDeleted::class, [SyncWidget::class, 'handleIntegrationDeleted']);

            Event::listen(IntegrationUpdated::class, [SyncWidget::class, 'handleIntegrationUpdated']);
        }
    }
}
