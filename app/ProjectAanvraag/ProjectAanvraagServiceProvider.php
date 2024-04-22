<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Auth0\Repositories\Auth0UserRepository;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\ProjectAanvraag\Listeners\CreateWidget;
use App\UiTiDv1\Events\ConsumerCreated;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;

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

        $this->app->singleton(CreateWidget::class, function () {
            $groups = config('uitidv1.environments.prod.groups.widgets');
            $groups = explode(',', str_replace(' ', '', $groups));

            return new CreateWidget(
                $this->app->get(ProjectAanvraagClient::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(ContactRepository::class),
                $this->app->get(UiTiDv1ConsumerRepository::class),
                (int) end($groups),
                $this->app->get(Auth0UserRepository::class),
                $this->app->get(LoggerInterface::class)
            );
        });

        if (config('project_aanvraag.create_widget', false)) {
            Event::listen(IntegrationCreated::class, [CreateWidget::class, 'handleIntegrationCreated']);
            Event::listen(ContactCreated::class, [CreateWidget::class, 'handleContactCreated']);
            Event::listen(ConsumerCreated::class, [CreateWidget::class, 'handleConsumerCreated']);

            Event::listen(IntegrationActivated::class, [CreateWidget::class, 'handleActivation']);
            Event::listen(IntegrationBlocked::class, [CreateWidget::class, 'handleBlock']);
            Event::listen(IntegrationDeleted::class, [CreateWidget::class, 'handleDelete']);

            //Event::listen(IntegrationUpdated::class);
        }
    }
}
