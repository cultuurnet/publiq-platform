<?php

declare(strict_types=1);

namespace App\Providers;

use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InsightlyClient::class, function () {
            return new InsightlyClient(
                new Client(
                    [
                        'base_uri' => config('insightly.host'),
                        'http_errors' => false,
                    ]
                ),
                config('insightly.api_key'),
                new Pipelines(config('insightly.pipelines'))
            );
        });
    }

    public function boot(): void
    {
    }
}
