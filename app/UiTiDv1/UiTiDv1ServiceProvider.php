<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use App\UiTiDv1\Repositories\EloquentUiTiDv1ConsumerRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use Illuminate\Support\Facades\App;
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
    }
}
