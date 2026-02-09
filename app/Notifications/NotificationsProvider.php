<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Notifications\Listeners\NotifyIntegrationChanged;
use App\Notifications\Slack\SlackMessageBuilder;
use App\Notifications\Slack\SlackNotifier;
use App\Search\SearchServiceProvider;
use App\Search\UdbOrganizerNameResolver;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class NotificationsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Notifier::class,
            fn () =>
            new SlackNotifier(
                config('slack.botToken'),
                config('slack.channels.publiq_platform'),
                config('slack.baseUri')
            )
        );

        $this->app->singleton(
            MessageBuilder::class,
            fn () =>
            new SlackMessageBuilder(
                $this->app->get(SubscriptionRepository::class),
                $this->app->get(UdbOrganizerNameResolver::class),
                $this->app->get(SearchServiceProvider::PROD_SEARCH_SERVICE),
                config(UiTPASConfig::CLIENT_PERMISSIONS_URI->value),
                config(UiTPASConfig::UDB_BASE_URI->value),
                config('app.url'),
            )
        );

        Event::listen(IntegrationCreated::class, [NotifyIntegrationChanged::class, 'handle']);
        Event::listen(IntegrationActivationRequested::class, [NotifyIntegrationChanged::class, 'handle']);
    }
}
