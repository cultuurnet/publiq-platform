<?php

declare(strict_types=1);

namespace App\Domain\Auth\AuthenticationStrategy;

use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Exception\KeycloakLoginFailed;
use App\Keycloak\Realm;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Lcobucci\JWT\UnencryptedToken;
use Psr\Log\LoggerInterface;

final readonly class KeycloakAuthenticationStrategy implements AuthenticationStrategy
{
    public function __construct(
        private Realm $realm,
        private KeycloakApiClient $keycloakApiClient,
        private LoggerInterface $logger,
        private string $redirectUri,
        private SessionManager $session
    ) {
    }

    public function getLoginUrl(array $loginParams): string
    {
        $state = uniqid('', true);

        $this->session->put('state', $state);

        $params = http_build_query([
                'client_id' => $this->realm->clientId,
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri,
                'scope' => 'openid',
                'state' => $state,
            ] + $loginParams);

        return $this->realm->baseUrl . 'realms/' . $this->realm->internalName . '/protocol/openid-connect/auth?' . $params;
    }

    public function exchange(Request $request): bool
    {
        try {
            if (empty($request['state']) || $request['state'] !== $this->session->get('state')) {
                throw KeycloakLoginFailed::stateMismatch();
            }

            if (empty($request['code'])) {
                throw KeycloakLoginFailed::missingCode();
            }

            if (empty($request['iss']) || $request['iss'] !== $this->realm->baseUrl) {
                throw KeycloakLoginFailed::issMismatch($request['iss'] ?? '');
            }

            $token = $this->keycloakApiClient->exchangeToken($this->realm, $request['code']);

            $this->session->put('token', $token);

            return true;
        } catch (KeyCloakApiFailed $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    public function getUser(): ?array
    {
        $jwt = $this->session->get('token');

        if(! $jwt instanceof UnencryptedToken) {
            return null;
        }

        return [
            'sub' => $jwt->claims()->get('sub'),
            'name' => $jwt->claims()->get('preferred_username') ?? $jwt->claims()->get('name'),
            'email' => $jwt->claims()->get('email'),
            'https://publiq.be/first_name' => $jwt->claims()->get('given_name'),
            'family_name' => $jwt->claims()->get('family_name'),
        ];
    }

    public function getIdToken(): string
    {
        $jwt = $this->session->get('token');

        if(! $jwt instanceof UnencryptedToken) {
            return '';
        }

        return $jwt->toString();
    }
}
