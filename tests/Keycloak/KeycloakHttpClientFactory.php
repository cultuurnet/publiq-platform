<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Api\TokenStrategy\ClientCredentials;
use App\Keycloak\Client\KeycloakGuzzleClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

trait KeycloakHttpClientFactory
{
    protected function givenKeycloakHttpClient(LoggerInterface $logger, MockHandler $mock): KeycloakGuzzleClient
    {
        $client = $this->givenClient($mock);

        return new KeycloakGuzzleClient(
            $client,
            new ClientCredentials(
                $client,
                $logger
            )
        );
    }

    protected function givenClient(MockHandler $mock): Client
    {
        return new Client(['handler' => HandlerStack::create($mock), RequestOptions::HTTP_ERRORS => false]);
    }
}
