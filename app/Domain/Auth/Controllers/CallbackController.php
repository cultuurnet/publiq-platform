<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\UserModel;
use App\Keycloak\KeycloakConfig;
use Auth0\SDK\Auth0;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final readonly class CallbackController
{
    /** @param array<string> $loginParams */
    public function __construct(private array $loginParams)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        if ($auth0->exchange()) {
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

    private function addAcrEnforcementParam(): array
    {
        $params = $this->loginParams;
        $params['acr_values'] = 'highest';
        unset($params['prompt']);
        return $params;
    }
}
