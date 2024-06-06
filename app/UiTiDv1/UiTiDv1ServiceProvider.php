<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\UiTiDv1\Jobs\UnblockConsumer;
use App\UiTiDv1\Jobs\UnblockConsumerHandler;
use App\UiTiDv1\Jobs\BlockConsumer;
use App\UiTiDv1\Jobs\BlockConsumerHandler;
use App\UiTiDv1\Jobs\CreateMissingConsumers;
use App\UiTiDv1\Jobs\CreateMissingConsumersHandler;
use App\UiTiDv1\Listeners\BlockConsumers;
use App\UiTiDv1\Listeners\CreateConsumers;
use App\UiTiDv1\Listeners\UnblockConsumers;
use App\UiTiDv1\Listeners\UpdateConsumers;
use App\UiTiDv1\Repositories\EloquentUiTiDv1ConsumerRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class UiTiDv1ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UiTiDv1ConsumerRepository::class, EloquentUiTiDv1ConsumerRepository::class);

        $this->app->singleton(UiTiDv1ClusterSDK::class, function () {
            // Filter out environments with missing config (consider them disabled)
            $environmentsConfig = array_filter(
                config('uitidv1.environments'),
                static fn (array $environmentConfig) =>
                    $environmentConfig['baseUrl'] !== '' &&
                    $environmentConfig['consumerKey'] !== '' &&
                    $environmentConfig['consumerSecret'] !== ''
            );

            // Create a cluster with an environment SDK per (enabled) environment config
            return new UiTiDv1ClusterSDK(
                ...array_map(
                    static fn (array $environmentConfig, string $environment) => new UiTiDv1EnvironmentSDK(
                        UiTiDv1Environment::from($environment),
                        UiTiDv1EnvironmentSDK::createOAuth1HttpClient(
                            $environmentConfig['baseUrl'],
                            $environmentConfig['consumerKey'],
                            $environmentConfig['consumerSecret']
                        ),
                        // Take the groups per integration type and convert each value from a comma-separated string
                        // to an array.
                        array_map(
                            static fn (string $groups) => explode(',', str_replace(' ', '', $groups)),
                            $environmentConfig['groups']
                        )
                    ),
                    $environmentsConfig,
                    array_keys($environmentsConfig)
                )
            );
        });

        $this->app->singleton(CachedUiTiDv1Status::class, function () {
            return new CachedUiTiDv1Status(
                App::get(UiTiDv1ClusterSDK::class)
            );
        });

        if (config('uitidv1.enabled')) {
            // By default, the UiTiD V1 integration is enabled. For testing purposes this can be disabled inside the .env file.

            // May always be registered even if there are no configured environments, because in that case the cluster SDK
            // will just not have any environment SDKs to loop over and so it simply won't do anything. But it won't crash either.
            Event::listen(IntegrationCreated::class, [CreateConsumers::class, 'handle']);
            Event::listen(IntegrationUpdated::class, [UpdateConsumers::class, 'handle']);
            Event::listen(IntegrationBlocked::class, [BlockConsumers::class, 'handle']);
            Event::listen(IntegrationUnblocked::class, [UnblockConsumers::class, 'handle']);
            Event::listen(IntegrationDeleted::class, [BlockConsumers::class, 'handle']);

            Event::listen(UnblockConsumer::class, [UnblockConsumerHandler::class, 'handle']);
            Event::listen(BlockConsumer::class, [BlockConsumerHandler::class, 'handle']);

            Event::listen(CreateMissingConsumers::class, [CreateMissingConsumersHandler::class, 'handle']);
        }
    }
}
