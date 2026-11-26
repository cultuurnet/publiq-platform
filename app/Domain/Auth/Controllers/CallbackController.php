<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\UserModel;
use App\Keycloak\KeycloakConfig;
use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\StateException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final readonly class CallbackController
{
    private const LOGIN_RETRIED = 'auth.login_retried';

    private const RECOVERABLE_STATE_ERRORS = [
        StateException::MSG_INVALID_STATE,
        StateException::MSG_MISSING_CODE,
        StateException::MSG_MISSING_CODE_VERIFIER,
        StateException::MSG_MISSING_NONCE,
    ];

    /** @param array<string> $loginParams */
    public function __construct(private array $loginParams)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        // exchange() purges the Auth0 session before it validates the state, and that session is the login.
        if (Auth::check()) {
            return redirect()->intended('/');
        }

        try {
            $exchanged = $auth0->exchange();
        } catch (StateException $exception) {
            if (!in_array($exception->getMessage(), self::RECOVERABLE_STATE_ERRORS, true)) {
                throw $exception;
            }

            return $this->recoverFromInvalidCallback($request);
        }

        Session::forget(self::LOGIN_RETRIED);

        if ($exchanged) {
            $user = $auth0->getUser();

            if ($user === null) {
                return redirect()->intended('/');
            }

            if (config(KeycloakConfig::KEYCLOAK_ENFORCE_2FA_FOR_ADMINS)) {
                $user['acr'] = $user['acr'] ?? null;
                if ($user['acr'] !== 'highest' && in_array($user['email'], config('nova.users'), true)) {
                    // Calling clear() is necessary here. Without it, the user will be  already logged in on the frontend but doesn’t have the correct ACR level for the admin, they get stuck in a redirect loop — they appear logged in, but don't meet the access requirements, so they keep getting kicked back without triggering a proper re-login with higher privileges.
                    $auth0->clear();

                    $url = $auth0->login(null, $this->addAcrEnforcementParam());

                    return redirect()->to($url);
                }
            }

            Session::put('id_token', $auth0->getIdToken());

            Auth::login(UserModel::fromSession($user));
        }

        return redirect()->intended('/');
    }

    /**
     * The state of a login is single-use and only lives in the browser that started it, so a second tab,
     * a bookmarked Keycloak page or another browser all end up here.
     * Keycloak usually still has a session, so a fresh login completes without the user noticing.
     * The login is only restarted once, so a browser that drops our cookies doesn't loop between us and Keycloak.
     */
    private function recoverFromInvalidCallback(Request $request): RedirectResponse
    {
        if ($request->query('error') !== null || Session::pull(self::LOGIN_RETRIED, false)) {
            return redirect('/');
        }

        Session::put(self::LOGIN_RETRIED, true);

        return redirect()->route('login');
    }

    private function addAcrEnforcementParam(): array
    {
        $params = $this->loginParams;
        $params['acr_values'] = 'highest';
        unset($params['prompt']);
        return $params;
    }
}
