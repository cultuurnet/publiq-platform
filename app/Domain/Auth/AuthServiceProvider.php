<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Controllers\AccessController;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Repositories\ContactRepository;
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

        $this->app->when(AccessController::class)
            ->needs('$adminEmails')
            ->give(config('nova.users'));

        Gate::define('access-integration', function (UserModel $user, string $integrationId): bool {
            /**
             * @var ContactRepository  $contactRepository
             */
            $contactRepository = $this->app->get(ContactRepository::class);

            $contacts = $contactRepository->getByIntegrationIdAndEmail(Uuid::fromString($integrationId), $user->email);

            return $contacts->count() > 0;
        });
    }
}
