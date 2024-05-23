<?php

declare(strict_types=1);

namespace App\Search;

use App\Search\Sapi3\Sapi3SearchService;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Serializer\Serializer;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

final class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Sapi3SearchService::class, function () {
            return new Sapi3SearchService(
                new SearchClient(
                    new Client([
                        'base_uri' => config('search.base_uri'),
                        'headers' => [
                            'X-Api-Key' => config('search.api_key'),
                        ],
                    ]),
                    new Serializer()
                )
            );
        });

        $this->app->bind(SearchService::class, Sapi3SearchService::class);
    }
}
