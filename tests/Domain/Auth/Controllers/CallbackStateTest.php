<?php

declare(strict_types=1);

namespace Tests\Domain\Auth\Controllers;

use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\StateException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Runs the real Auth0 SDK: the `state` of a login is single-use and only lives
 * in the transient cookie of the browser that started the login.
 */
final class CallbackStateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_COOKIE = [];
        $_GET = [];

        Config::set('keycloak.login.domain', 'keycloak.example.com');
        Config::set('keycloak.login.clientId', 'platform');
        Config::set('keycloak.login.clientSecret', 'client-secret');
        Config::set('keycloak.login.cookieSecret', str_repeat('s', 32));
        Config::set('keycloak.login.redirectUri', 'http://localhost/auth/callback');
        Config::set('keycloak.login.httpClient', new Client([
            'handler' => HandlerStack::create(new MockHandler([new Response(400)])),
        ]));

        $this->app->forgetInstance(Auth0::class);

        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        $_COOKIE = [];
        $_GET = [];

        parent::tearDown();
    }

    public function test_callback_of_a_login_started_in_another_browser_restarts_the_login(): void
    {
        $this->hitCallback(['code' => 'abc', 'state' => 'state-from-another-browser'])
            ->assertRedirect(route('login'));
    }

    public function test_callback_of_a_login_overwritten_by_a_second_login_restarts_the_login(): void
    {
        $firstState = $this->startLogin();
        $this->startLogin();

        $this->hitCallback(['code' => 'abc', 'state' => $firstState])
            ->assertRedirect(route('login'));
    }

    public function test_replayed_callback_restarts_the_login(): void
    {
        $state = $this->startLogin();

        try {
            $this->hitCallback(['code' => 'abc', 'state' => $state]);
            $this->fail('The code exchange should have failed');
        } catch (StateException $exception) {
            // The state was accepted; a failing code exchange is not an invalid callback, so it is still reported.
            $this->assertSame(StateException::MSG_FAILED_CODE_EXCHANGE, $exception->getMessage());
        }

        $this->hitCallback(['code' => 'abc', 'state' => $state])
            ->assertRedirect(route('login'));
    }

    public function test_replayed_callback_of_a_logged_in_user_keeps_them_logged_in(): void
    {
        $user = ['sub' => 'auth0|123', 'email' => 'john.doe@test.com', 'name' => 'John Doe'];
        app(Auth0::class)->setUser($user);
        // Logged in the way CallbackController does it: the guard keeps the id, UserProvider reads the Auth0 session.
        $this->withSession([Auth::guard()->getName() => $user['sub']]);

        $this->hitCallback(['code' => 'abc', 'state' => 'already-used-state'])
            ->assertRedirect('/');

        $this->assertSame($user, app(Auth0::class)->getUser());
    }

    public function test_login_is_restarted_only_once(): void
    {
        $this->hitCallback(['code' => 'abc', 'state' => 'state-from-another-browser'])
            ->assertRedirect(route('login'));

        $this->hitCallback(['code' => 'abc', 'state' => 'state-from-another-browser'])
            ->assertRedirect('/');

        $this->hitCallback(['code' => 'abc', 'state' => 'state-from-another-browser'])
            ->assertRedirect(route('login'));
    }

    public function test_callback_with_an_error_from_keycloak_goes_home(): void
    {
        $state = $this->startLogin();

        $this->hitCallback(['error' => 'access_denied', 'state' => $state])
            ->assertRedirect('/');
    }

    /**
     * The SDK reads the callback parameters from $_GET instead of the Laravel request.
     */
    private function hitCallback(array $query): TestResponse
    {
        $_GET = $query;

        return $this->get('/auth/callback?' . http_build_query($query));
    }

    private function startLogin(): string
    {
        $location = $this->get('/admin/login')->headers->get('Location');
        parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $query);

        $this->assertIsString($query['state'] ?? null);

        return $query['state'];
    }
}
