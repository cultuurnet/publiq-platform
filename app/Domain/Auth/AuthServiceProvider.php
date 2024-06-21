<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\AuthenticationStrategy\Auth0AuthenticationStrategy;
use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use App\Domain\Auth\AuthenticationStrategy\KeycloakAuthenticationStrategy;
use App\Domain\Auth\Controllers\AccessController;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Environment;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\KeycloakConfig;
use App\Keycloak\Realm;
use Auth0\SDK\Auth0;
use Auth0\SDK\Contract\Auth0Interface;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticationStrategy::class, function () {
            if (config('auth.mode') === 'keycloak') {
                return new KeycloakAuthenticationStrategy(
                    new Realm(
                        config(KeycloakConfig::LOGIN_REALM_NAME),
                        config(KeycloakConfig::LOGIN_REALM_NAME),
                        config(KeycloakConfig::LOGIN_BASE_URL),
                        config(KeycloakConfig::LOGIN_CLIENT_ID),
                        config(KeycloakConfig::LOGIN_CLIENT_SECRET),
                        Environment::fromName(env('APP_ENV')) ?? throw new \Exception('Could not determine environment')
                    ),
                    App::get(ApiClient::class),
                    App::get(LoggerInterface::class),
                    config(KeycloakConfig::REDIRECT_URI),
                    $this->app->get(SessionManager::class)
                );
            }

            return new Auth0AuthenticationStrategy(App::get(Auth0::class));
        });

        $this->app->bind(Auth0Interface::class, Auth0::class);

        /** @var array $adminEmails */
        $adminEmails = config('nova.users');

        $this->app->when(AccessController::class)
            ->needs('$adminEmails')
            ->give($adminEmails);

        Gate::define('access-integration', function (UserModel $user, string $integrationId) use ($adminEmails): bool {
            if (in_array($user->email, $adminEmails)) {
                return true;
            }

            /**
             * @var ContactRepository $contactRepository
             */
            $contactRepository = $this->app->get(ContactRepository::class);

            $contacts = $contactRepository->getByIntegrationIdAndEmail(Uuid::fromString($integrationId), $user->email);

            return $contacts->count() > 0;
        });
    }
}
