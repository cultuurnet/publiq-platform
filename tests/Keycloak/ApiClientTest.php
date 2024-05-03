<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\ApiClient;
use App\Keycloak\Dto\Config;
use App\Keycloak\Dto\Realm;
use App\Keycloak\TokenStrategy\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ApiClientTest extends TestCase
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
            new Response(200, [], json_encode(
                [
                    [
                        'id' => '12IFF91JFIUM',
                        'clientId' => 'keycloak|1',
                        'name' => 'test client',
                        'surrogateAuthRequired' => true,
                        'enabled' => true,
                        'alwaysDisplayInConsole' => true,
                        'clientAuthenticatorType' => 'password',
                        'redirectUris' => ['https://localhost/'],
                        'webOrigins' => ['https://localhost/'],
                        'notBefore' => 1,
                        'bearerOnly' => true,
                        'consentRequired' => true,
                        'standardFlowEnabled' => true,
                        'implicitFlowEnabled' => true,
                        'serviceAccountsEnabled' => true,
                        'directAccessGrantsEnabled' => true,
                        'publicClient' => true,
                        'frontchannelLogout' => true,
                        'protocol' => 'http',
                        'fullScopeAllowed' => true,
                        'nodeReRegistrationTimeout' => 30,
                        'defaultClientScopes' => ['SAPI3'],

                    ],
                ],
                JSON_THROW_ON_ERROR
            )),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $integration = new ApiClient(
            $client,
            $this->config,
            new ClientCredentials($client, $this->config),
            $this->createMock(LoggerInterface::class)
        );

        $clients = $integration->fetchClients('123456');

        $firstClient = $clients['uitidpoc']->first();
        $this->assertEquals('12IFF91JFIUM', $firstClient->getId());
        $this->assertEquals('keycloak|1', $firstClient->getClientId());
        $this->assertEquals('test client', $firstClient->getName());
    }

    public function testFailureToFetchClients(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'pqeaefosdfhbsdq'], JSON_THROW_ON_ERROR)),
            new Response(500, [], 'It is broken'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Server error: `GET https://keycloak.example.com/admin/realms/uitidpoc/clients?clientId=123456` resulted in a `500 Internal Server Error` response:' . PHP_EOL . 'It is broken' . PHP_EOL);

        $integration = new ApiClient(
            $client,
            $this->config,
            new ClientCredentials($client, $this->config),
            $logger
        );

        $clients = $integration->fetchClients('123456');
        $this->assertEmpty($clients['uitidpoc']);
    }
}
