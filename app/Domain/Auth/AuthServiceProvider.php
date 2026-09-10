<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Controllers\AccessController;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Auth\Repositories\UserRepository;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use App\Keycloak\Repositories\KeycloakUserRepository;
use Auth0\SDK\Auth0;
use Auth0\SDK\Contract\Auth0Interface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Auth0Interface::class, Auth0::class);

        $this->app->bind(
            UserRepository::class,
            KeycloakUserRepository::class
        );

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
             * @var ContactRepository  $contactRepository
             */
            $contactRepository = $this->app->get(ContactRepository::class);

            $contacts = $contactRepository->getByIntegrationIdAndEmail(Uuid::fromString($integrationId), $user->email);

            return $contacts->count() > 0;
        });

        Gate::define('delete-integration-url', function (UserModel $user, string $integrationId, string $urlId): bool {
            $integrationUrlModel = IntegrationUrlModel::query()
                ->where('id', $urlId)
                ->where('integration_id', $integrationId)
                ->firstOrFail();

            return $integrationUrlModel->toDomain()->type->isDeletable();
        });

        Gate::define('delete-contact', function (UserModel $user, string $integrationId, string $contactId): bool {
            $contactModel = ContactModel::query()
                ->where('id', $contactId)
                ->where('integration_id', $integrationId)
                ->firstOrFail();

            return ContactType::from($contactModel->type)->isDeletable();
        });
    }
}
