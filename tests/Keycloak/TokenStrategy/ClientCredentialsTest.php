<?php

declare(strict_types=1);

namespace Tests\Keycloak\TokenStrategy;

use App\Keycloak\Dto\Config;
use App\Keycloak\Dto\Realm;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ClientCredentialsTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config(
            true,
            'https://keycloak.example.com/',
            'php_client',
            'a_true_secret',
            new Realm('uitidpoc', 'Acceptance')
        );
    }

    public function testHappyPath(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'pqeaefosdfhbsdq'], JSON_THROW_ON_ERROR)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientCredentials = new ClientCredentials(new Client(['handler' => $handlerStack]), $this->config);
        $token = $clientCredentials->fetchToken(Realm::getMasterRealm());

        $this->assertEquals('pqeaefosdfhbsdq', $token);
    }

    public function testFailedBecauseIntegrationIsDisabled(): void
    {
        $config = new Config(
            false,
            'https://keycloak.example.com/',
            'php_client',
            'a_true_secret',
            new Realm('uitidpoc', 'Acceptance')
        );

        $client = new ClientCredentials(new Client(), $config);
        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::IS_DISABLED);

        $client->fetchToken(Realm::getMasterRealm());
    }

    public function testFailedBecauseResponseDidNotContainToken(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientCredentials = new ClientCredentials(new Client(['handler' => $handlerStack]), $this->config);

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::UNEXPECTED_TOKEN_RESPONSE);

        $token = $clientCredentials->fetchToken(Realm::getMasterRealm());

        $this->assertEquals('pqeaefosdfhbsdq', $token);
    }

    public function testFailedBecauseUnauthorized(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([], JSON_THROW_ON_ERROR)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientCredentials = new ClientCredentials(new Client(['handler' => $handlerStack]), $this->config);

        $this->expectException(KeyCloakApiFailed::class);
        $this->expectExceptionCode(KeyCloakApiFailed::COULD_NOT_FETCH_ACCESS_TOKEN);
        $token = $clientCredentials->fetchToken(Realm::getMasterRealm());
    }
}
