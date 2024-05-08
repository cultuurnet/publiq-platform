<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Client\KeycloakClientWithBearer;
use App\Keycloak\Client\KeycloakClientWithoutBearer;
use App\Keycloak\Config;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

trait KeycloakHelper
{
    private Config $config;

    protected function createKeycloakClientWithBearer(LoggerInterface $logger, ?MockHandler $mock = null): KeycloakClientWithBearer
    {
        return new KeycloakClientWithBearer(
            $this->createClient($mock),
            $this->config,
            new ClientCredentials(
                $this->createKeycloakClientWithoutBearer($mock),
                $this->config,
                $logger
            )
        );
    }

    protected function createKeycloakClientWithoutBearer(?MockHandler $mock = null): KeycloakClientWithoutBearer
    {
        return new KeycloakClientWithoutBearer(
            $this->createClient($mock),
            $this->config
        );
    }

    private function createClient(?MockHandler $mock): Client
    {
        return new Client($mock ? ['handler' => HandlerStack::create($mock), RequestOptions::HTTP_ERRORS => false] : [RequestOptions::HTTP_ERRORS => false]);
    }
}
