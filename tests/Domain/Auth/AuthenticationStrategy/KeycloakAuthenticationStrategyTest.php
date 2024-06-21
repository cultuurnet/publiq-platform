<?php

declare(strict_types=1);

namespace Tests\Domain\Auth\AuthenticationStrategy;

use App\Domain\Auth\AuthenticationStrategy\KeycloakAuthenticationStrategy;
use App\Json;
use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\Exception\KeycloakLoginFailed;
use App\Keycloak\KeycloakConfig;
use App\Keycloak\Realm;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Lcobucci\JWT\Token\Plain;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\Domain\Auth\AuthenticationStrategy\Mock\SessionManagerMock;
use Tests\Domain\Auth\JwtTestProvider;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class KeycloakAuthenticationStrategyTest extends TestCase
{
    use RealmFactory;
    use KeycloakHttpClientFactory;

    private KeycloakApiClient $keycloakApiClient;
    private LoggerInterface&MockObject $logger;
    private Realm $realm;
    private string $certificate;
    private SessionManager $session;
    private string $jwt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = new SessionManagerMock();

        $this->realm = $this->givenRealmNoScopeConfig();
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->certificate = (string)file_get_contents(config(KeycloakConfig::CERTIFICATE));
        $this->jwt = (new JwtTestProvider())->getJwt();
    }

    public function test_get_login_url(): void
    {
        $loginParams = ['scope' => 'openid'];
        $loginUrl = $this->givenKeycloakAuthenticationStrategy(new MockHandler([]))
            ->getLoginUrl($loginParams);

        $this->assertStringContainsString('https://keycloak.com/api/realms/myAcceptanceRealm/protocol/openid-connect/auth?', $loginUrl);
        $this->assertStringContainsString('client_id=php_client', $loginUrl);
        $this->assertStringContainsString('response_type=code', $loginUrl);
        $this->assertStringContainsString('redirect_uri=' . urlencode('https://publiq.be/callback'), $loginUrl);
        $this->assertStringContainsString('scope=openid', $loginUrl);
        $this->assertStringContainsString('state=' . $this->session->get('state'), $loginUrl);
    }

    public function test_exchange_success(): void
    {
        $authStrategy = $this->doSuccessfulTokenExchange();

        $token = $this->session->get('token');
        $this->assertInstanceOf(Plain::class, $token);

        $jwt = Json::decodeAssociatively($this->jwt);
        $this->assertEquals($jwt['access_token'], $token->toString());
        $this->assertEquals($token->toString(), $authStrategy->getIdToken());
    }

    public function test_get_user(): void
    {
        $authStrategy = $this->doSuccessfulTokenExchange();

        $user = $authStrategy->getUser();

        $this->assertIsArray($user);

        $this->assertEquals('c728de2f-ed75-4189-9bfa-550f665a553c', $user['sub']);
        $this->assertEquals('koen.eelen@publiq.be', $user['name']);
        $this->assertEquals('koen.eelen@publiq.be', $user['email']);
        $this->assertEquals('koen', $user['https://publiq.be/first_name']);
        $this->assertEquals('Eelen', $user['family_name']);
    }

    public function test_exchange_state_mismatch(): void
    {
        $this->expectException(KeycloakLoginFailed::class);
        $this->expectExceptionCode(KeycloakLoginFailed::STATE_IS_INVALID);

        $authStrategy = $this->givenKeycloakAuthenticationStrategy(new MockHandler([
            new Response(200, [], $this->jwt),
        ]));
        $request = new Request(['state' => 'wrong-state']);
        $this->session->put('state', 'test-state');

        $authStrategy->exchange($request);
    }

    public function test_exchange_missing_code(): void
    {
        $this->expectException(KeycloakLoginFailed::class);
        $this->expectExceptionCode(KeycloakLoginFailed::MISSING_CODE);

        $authStrategy = $this->givenKeycloakAuthenticationStrategy(new MockHandler([
            new Response(200, [], $this->jwt),
        ]));
        $request = new Request(['state' => 'test-state']);
        $this->session->put('state', 'test-state');

        $authStrategy->exchange($request);
    }

    public function test_exchange_iss_mismatch(): void
    {
        $this->expectException(KeycloakLoginFailed::class);
        $this->expectExceptionCode(KeycloakLoginFailed::ISS_MISMATCH);

        $authStrategy = $this->givenKeycloakAuthenticationStrategy(new MockHandler([
            new Response(200, [], $this->jwt),
        ]));
        $request = new Request([
            'state' => 'test-state',
            'code' => 'test-code',
            'iss' => 'https://wrong-issuer.example.com/',
        ]);

        $this->session->put('state', 'test-state');

        $authStrategy->exchange($request);
    }

    public function test_exchange_keycloak_api_failed(): void
    {
        $request = new Request([
            'state' => 'test-state',
            'code' => 'test-code',
            'iss' => 'https://keycloak.com/api/',
        ]);

        $errorMsg = 'ja lap, tis kapot';
        $authStrategy = $this->givenKeycloakAuthenticationStrategy(new MockHandler([
            new Response(500, [], $errorMsg),
        ]));

        $this->session->put('state', 'test-state');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to exchange token: ' . $errorMsg);

        $this->assertFalse($authStrategy->exchange($request));
    }

    private function givenKeycloakAuthenticationStrategy(MockHandler $mock): KeycloakAuthenticationStrategy
    {
        $this->keycloakApiClient = new KeycloakApiClient(
            $this->givenKeycloakHttpClient($this->logger, $mock),
            $this->givenAllRealms(),
            $this->logger,
            $this->certificate,
        );

        return new KeycloakAuthenticationStrategy(
            $this->realm,
            $this->keycloakApiClient,
            $this->logger,
            'https://publiq.be/callback',
            $this->session
        );
    }

    private function doSuccessfulTokenExchange(): KeycloakAuthenticationStrategy
    {
        $authStrategy = $this->givenKeycloakAuthenticationStrategy(new MockHandler([
            new Response(200, [], $this->jwt),
        ]));

        $request = new Request([
            'state' => 'test-state',
            'code' => 'test-code',
            'iss' => 'https://keycloak.com/api/',
        ]);

        $this->session->put('state', 'test-state');
        $this->assertTrue($authStrategy->exchange($request));

        return $authStrategy;
    }
}
