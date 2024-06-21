<?php

declare(strict_types=1);

namespace App\Domain\Auth\AuthenticationStrategy;

use Auth0\SDK\Auth0;
use Auth0\SDK\Token;
use Illuminate\Http\Request;

final readonly class Auth0AuthenticationStrategy implements AuthenticationStrategy
{
    public function __construct(private Auth0 $auth0)
    {
    }

    public function getLoginUrl(array $loginParams): string
    {
        $this->auth0->clear();

        return $this->auth0->login(null, $loginParams);
    }

    public function exchange(Request $request): bool
    {
        return $this->auth0->exchange(
            $request->get('redirectUrl'),
            $request->get('code'),
            $request->get('state')
        );
    }

    public function getUser(): ?array
    {
        return $this->auth0->getUser();
    }

    public function getIdToken(): string
    {
        return $this->auth0->getIdToken() ?? '';
    }

    public function logout(): void
    {
        $this->auth0->logout();
    }

    public function getLogoutLink(string $url): string
    {
        return $this->auth0->authentication()->getLogoutLink($url);
    }

    public function createToken(string $idToken): array
    {
        $token = new Token($this->auth0->configuration(), $idToken, Token::TYPE_ID_TOKEN);
        return $token->toArray();
    }
}
