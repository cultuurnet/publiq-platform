<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Domain\Auth\CurrentUser;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\ProjectAanvraag\Listeners\CreateWidget;
use App\UiTiDv1\Events\ConsumerCreated;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class ProjectAanvraagServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProjectAanvraagClient::class, function () {
            return new ProjectAanvraagClient(
                new Client(
                    [
                        'base_uri' => config('project_aanvraag.base_uri'),
                        'http_errors' => false,
                    ]
                ),
                $this->app->get(LoggerInterface::class)
            );
        });

        $this->app->singleton(CreateWidget::class, function () {
            // TODO: Use correct environment
            $groups = config('uitidv1.environments.prod.groups.widgets');
            $groups = explode(',', str_replace(' ', '', $groups));

            return new CreateWidget(
                $this->app->get(ProjectAanvraagClient::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(ContactRepository::class),
                $this->app->get(UiTiDv1ConsumerRepository::class),
                (int) end($groups),
                $this->app->get(CurrentUser::class),
                $this->app->get(LoggerInterface::class)
            );
        });

        if (config('project_aanvraag.create_widget', false)) {
            Event::listen(IntegrationCreated::class, [CreateWidget::class, 'handleIntegrationCreated']);
            Event::listen(ContactCreated::class, [CreateWidget::class, 'handleContactCreated']);
            Event::listen(ConsumerCreated::class, [CreateWidget::class, 'handleConsumerCreated']);
        }
    }
}
