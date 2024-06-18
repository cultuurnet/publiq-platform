<?php

declare(strict_types=1);

namespace App\Domain\Auth\AuthenticationStrategy;

use App\Keycloak\Client\KeycloakApiClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Exception\KeycloakLoginFailed;
use App\Keycloak\Realm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Psr\Log\LoggerInterface;

// Demo: https://app.kcpoc.lodgon.com/app/
// https://account.kcpoc.lodgon.com/admin/master/console/#/uitidpoc/clients/8d3109f6-c7e7-4ecd-a7b5-c49ac92edfc3/settings
// https://account.kcpoc.lodgon.com/admin/master/console/#/master/clients/d58afad3-31c1-402a-a4bb-cb83cd1b74b1/settings
final readonly class KeycloakAuthenticationStrategy implements AuthenticationStrategy
{
    public function __construct(private Realm $realm, private KeycloakApiClient $keycloakApiClient, private LoggerInterface $logger)
    {
    }

    public function getLoginUrl(array $loginParams): string
    {
        $state = uniqid('', true);

        // @todo is there a cleaner way to work with sessions in Laravel?
        Session::put('state', $state);

        $params = http_build_query([
                'client_id' => $this->realm->clientId,
                'response_type' => 'code',
                'redirect_uri' => env('KEYCLOAK_LOGIN_REDIRECT_URI'),
                'scope' => 'openid',
                'state' => $state,
            ] + $loginParams);

        return $this->realm->baseUrl . 'realms/' . $this->realm->internalName . '/protocol/openid-connect/auth?' . $params;
    }

    public function exchange(Request $request): bool
    {
        try {
            if (empty($request['state']) || $request['state'] !== Session::get('state')) {
                throw KeycloakLoginFailed::stateMismatch();
            }

            if (empty($request['code'])) {
                throw KeycloakLoginFailed::missingCode();
            }

            if (empty($request['iss']) || $request['iss'] !== $this->realm->baseUrl) {
                throw KeycloakLoginFailed::issMismatch($request['iss'] ?? '');
            }

            //  array(4) { ["session_state"]=> string(36) "56ebbbbe-b694-47d9-8055-e660a0d6030d"}

            return $this->keycloakApiClient->exchangeToken($this->realm, $request['code']);
        } catch (KeyCloakApiFailed $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function getUser(): ?array
    {
        return null;


        /*
         * array:18 [▼ // app/Domain/Auth/Controllers/CallbackController.php:27
  "https://publiq.be/uitidv1id" => "813ff497-f0b8-4a59-bd44-dc2646ad98ff"
  "https://publiq.be/first_name" => "Koen"
  "https://publiq.be/postal_code" => "2900"
  "given_name" => "Koen"
  "nickname" => "koen.eelen"
  "name" => "koen.eelen@publiq.be"
  "picture" => "https://s.gravatar.com/avatar/84e08934e5f109fdfcc97fb05d5046cb?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fko.png"
  "updated_at" => "2024-06-13T09:22:04.839Z"
  "email" => "koen.eelen@publiq.be"
  "email_verified" => true
  "iss" => "https://account-acc.uitid.be/"
  "aud" => "HxhCZhDPpj9kdW7biTWEL4uHQfnpud4G"
  "iat" => 1718270527
  "exp" => 1718277727
  "sub" => "auth0|813ff497-f0b8-4a59-bd44-dc2646ad98ff"
  "auth_time" => 1718270524
  "sid" => "ou9AmMjPfDCyqXIcMrKrk_lrL5GcFYz-"
  "nonce" => "ef350f8d8cc42b4a24399b14b1e15f68"
]
         * */
    }

    public function getIdToken(): string
    {
        return Session::get('token');
        // TODO: Implement getIdToken() method.
    }
}
/*

https://account.kcpoc.lodgon.com/auth/realms/uitidpoc/protocol/openid-connect/auth?client_id=koen_test&response_type=code&redirect_uri=http%3A%2F%2Flocalhost%3A5555&scope=openid

werkt:
https://account.kcpoc.lodgon.com/realms/uitidpoc/protocol/openid-connect/auth?response_type=code&client_id=javaeebackend&redirect_uri=https%3A%2F%2Fapp.kcpoc.lodgon.com%2Fapp%2Frest%2Fauth%2Fcallback&scope=openid
*/
