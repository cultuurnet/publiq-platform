<?php

declare(strict_types=1);

namespace Tests\Keycloak\Repositories;

use App\Api\TokenStrategy\TokenStrategy;
use App\Domain\Integrations\Environment;
use App\Json;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Realm;
use App\Keycloak\Repositories\KeycloakUserRepository;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;

final class KeycloakUserRepositoryTest extends TestCase
{
    private KeycloakUserRepository $keycloakUserRepository;

    private ClientInterface&MockObject $client;

    private TokenStrategy&MockObject $tokenStrategy;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);

        $this->tokenStrategy = $this->createMock(TokenStrategy::class);

        $keycloakHttpClient = new KeycloakGuzzleClient(
            $this->client,
            $this->tokenStrategy,
        );

        $realm = new Realm(
            'myAcceptanceRealm',
            'Acc',
            'https://keycloak.com/api',
            'php_client',
            'dfgopopzjcvijogdrg',
            Environment::Acceptance,
            new ScopeConfig(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()),
        );

        $this->keycloakUserRepository = new KeycloakUserRepository($keycloakHttpClient, $realm);
    }

    public function test_it_searches_users_by_email(): void
    {
        $this->client->expects($this->once())
            ->method('send')
            ->with($this->callback(function (RequestInterface $request): bool {
                $this->assertEquals('GET', $request->getMethod());
                $this->assertEquals('/api/admin/realms/myAcceptanceRealm/users', $request->getUri()->getPath());
                $this->assertEquals('email=john%40publiq.be&exact=true', $request->getUri()->getQuery());
                $this->assertEquals('Bearer token', $request->getHeaderLine('Authorization'));
                return true;
            }))
            ->willReturn(
                new Response(
                    200,
                    [],
                    Json::encode(
                        [
                            [
                                'id' => 'google-oauth2|105581372645959335478',
                                'username' => 'john@publiq.be',
                                'firstName' => 'John',
                                'email' => 'john@publiq.be',
                            ],
                        ]
                    )
                )
            );

        $this->tokenStrategy->expects($this->once())
            ->method('fetchToken')
            ->willReturn('token');

        $userId = $this->keycloakUserRepository->findUserIdByEmail('john@publiq.be');

        $this->assertEquals('google-oauth2|105581372645959335478', $userId);
    }
}
