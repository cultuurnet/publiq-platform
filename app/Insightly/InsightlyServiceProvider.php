<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Insightly\Listeners\CreateContact;
use App\Insightly\Listeners\CreateOpportunity;
use App\Insightly\Repositories\EloquentInsightlyMappingRepository;
use App\Insightly\Repositories\InsightlyMappingRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class InsightlyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InsightlyMappingRepository::class, EloquentInsightlyMappingRepository::class);

        $this->app->singleton(InsightlyClient::class, function () {
            return new InsightlyClient(
                new Client(
                    [
                        'base_uri' => config('insightly.host'),
                        'http_errors' => false,
                    ]
                ),
                config('insightly.api_key') ?? '',
                new Pipelines(config('insightly.pipelines'))
            );
        });

        if (!empty(config('insightly.api_key'))) {
            Event::listen(IntegrationCreated::class, [CreateOpportunity::class, 'handle']);
            Event::listen(ContactCreated::class, [CreateContact::class, 'handle']);
        }
    }

    public function boot(): void
    {
    }
}
