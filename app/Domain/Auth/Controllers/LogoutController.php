<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Keycloak\KeycloakConfig;
use Auth0\SDK\Auth0;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController
{
    private function getLogoutLink(): string
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        $idtoken = $auth0->getIdToken();

        if (Auth::check()) {
            $auth0->logout();
            Auth::guard(config('nova.guard'))->logout();
        }

        $url = config('app.url');

        return sprintf(
            'https://%s/realms/%s/protocol/openid-connect/logout?client_id=%s&post_logout_redirect_uri=%s&id_token_hint=%s',
            config(KeycloakConfig::KEYCLOAK_DOMAIN),
            config(KeycloakConfig::KEYCLOAK_REALM_NAME),
            config(KeycloakConfig::KEYCLOAK_CLIENT_ID),
            $url,
            $idtoken
        );
    }

    public function adminLogout(): JsonResponse
    {
        $logoutLink = $this->getLogoutLink();

        return new JsonResponse([
            'redirect' => $logoutLink,
        ]);
    }

    public function inertiaLogout(): Response
    {
        $logoutLink = $this->getLogoutLink();

        return Inertia::location($logoutLink);
    }
}
