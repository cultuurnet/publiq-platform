<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth0\Listeners\CreateClients;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class InsightlyServiceProvider extends ServiceProvider
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

        Event::listen(IntegrationCreated::class, [CreateClients::class, 'handle']);
    }

    public function boot(): void
    {
    }
}
