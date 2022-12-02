<?php

declare(strict_types=1);

namespace App\Providers;

use App\Insightly\InsightlyClient;
use App\Insightly\Pipelines;
use App\Json;
use Auth0\SDK\API\Management;
use Auth0\SDK\Auth0;
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

        $this->app->singleton(Management::class, function () {
            $auth0 = new Auth0([
                'domain' => config('auth0.management.domain'),
                'clientId' => config('auth0.management.clientId'),
                'clientSecret' => config('auth0.management.clientSecret'),
                'cookieSecret' => config('auth0.management.cookieSecret'),
            ]);

            $response = $auth0->authentication()->clientCredentials([
                'audience' => config('auth0.management.audience'),
            ]);

            $response = Json::decodeAssociatively((string) $response->getBody());
            $auth0->configuration()->setManagementToken($response['access_token']);

            return $auth0->management();
        });
    }

    public function boot(): void
    {
    }
}
