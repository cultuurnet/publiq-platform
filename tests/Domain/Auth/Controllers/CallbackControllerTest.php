<?php

declare(strict_types=1);

namespace Tests\Domain\Auth\Controllers;

use App\Domain\Auth\Controllers\CallbackController;
use App\Keycloak\KeycloakConfig;
use Auth0\SDK\Auth0;
use Auth0\SDK\Contract\Auth0Interface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class CallbackControllerTest extends TestCase
{
    private string $loginUrl;
    private CallbackController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('nova.users', ['admin@publiq.be']);
        Config::set(KeycloakConfig::KEYCLOAK_ENFORCE_2FA_FOR_ADMINS, true);

        Config::set('session.driver', 'null');//set driver to NULL instead of mocking

        $this->loginUrl = 'https://acc.keycloak.publiq.com/login';

        $this->controller = new CallbackController([
            'clientId' => '123',
            'prompt' => 'login',
        ]);
    }

    public function test_redirects_to_acr_highest_login_if_user_known_but_not_highest_acr(): void
    {
        $auth0 = $this->createMock(Auth0Interface::class);
        $auth0->method('exchange')->willReturn(true);
        $auth0->method('getUser')->willReturn([
            'email' => 'admin@publiq.be',
            'acr' => 'standard',
        ]);
        $auth0->expects($this->once())->method('clear');
        $auth0->method('login')->willReturn($this->loginUrl);
        app()->instance(Auth0::class, $auth0);

        $redirect = $this->createMock(Redirector::class);
        $redirect->expects($this->once())->method('to')->with($this->loginUrl)->willReturn(new RedirectResponse($this->loginUrl));
        app()->instance('redirect', $redirect);

        $request = Request::create('/auth/callback');

        $response = $this->controller->__invoke($request);

        $this->assertEquals($this->loginUrl, $response->getTargetUrl());
    }

    public function test_logs_in_user_with_highest_acr_and_redirects_home(): void
    {
        $this->givenAuth0Service([
            'email' => 'admin@publiq.be',
            'acr' => 'highest',
        ]);

        Auth::shouldReceive('login')->once();

        Config::set('nova.users', []);

        $request = Request::create('/auth/callback');

        $this->givenRedirect();

        $response = $this->controller->__invoke($request);

        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function test_user_not_in_nova_users_gets_logged_in(): void
    {
        $this->givenAuth0Service([
            'email' => 'unknown@example.com',
            'acr' => 'lowest',
        ]);

        Auth::shouldReceive('login')->once();

        $this->givenRedirect();

        $request = Request::create('/auth/callback');
        $response = $this->controller->__invoke($request);

        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function test_redirects_home_if_exchange_fails(): void
    {
        $auth0 = $this->createMock(Auth0Interface::class);
        $auth0->method('exchange')->willReturn(false);
        app()->instance(Auth0::class, $auth0);

        $this->givenRedirect();

        $request = Request::create('/auth/callback');

        $response = $this->controller->__invoke($request);

        $this->assertEquals('/', $response->getTargetUrl());
    }

    private function givenRedirect(): void
    {
        $redirect = $this->createMock(Redirector::class);
        $redirect->expects($this->once())->method('intended')->with('/')->willReturn(new RedirectResponse('/'));
        app()->instance('redirect', $redirect);
    }

    private function givenAuth0Service(array $mockUser): void
    {
        $auth0 = $this->createMock(Auth0Interface::class);
        $auth0->method('exchange')->willReturn(true);
        $auth0->method('getUser')->willReturn($mockUser);
        $auth0->method('getIdToken')->willReturn('mock-id-token');
        app()->instance(Auth0::class, $auth0);
    }
}
