<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Client\KeycloakHttpClient;
use App\Keycloak\Config;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

trait KeycloakHttpClientFactory
{
    private Config $config;

    protected function givenKeycloakHttpClient(LoggerInterface $logger, MockHandler $mock): KeycloakHttpClient
    {
        return new KeycloakHttpClient(
            $this->givenClient($mock),
            $this->config,
            new ClientCredentials(
                $this->config,
                $logger
            )
        );
    }

    private function givenClient(MockHandler $mock): Client
    {
        return new Client(['handler' => HandlerStack::create($mock), RequestOptions::HTTP_ERRORS => false]);
    }
}
