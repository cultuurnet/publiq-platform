<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Keycloak\Collection\RealmCollection;
use App\Keycloak\Dto\Config;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class KeycloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiClient::class, function () {
            $httpClient = new Client(
                [
                    RequestOptions::HTTP_ERRORS => false,
                ]
            );

            $config = new Config(
                config('keycloak.enable'),
                config('keycloak.url'),
                config('keycloak.client_id'),
                config('keycloak.client_secret'),
                ... RealmCollection::getDefaultRealms()->toArray(),
            );
            return new ApiClient(
                $httpClient,
                $config,
                new ClientCredentials($httpClient, $config),
                $this->app->get(LoggerInterface::class),
            );
        });
    }
}
