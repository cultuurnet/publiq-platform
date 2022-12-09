<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0ClientsForIntegration;
use App\Auth0\Models\Auth0ClientModel;
use Ramsey\Uuid\UuidInterface;

final class Auth0ClientRepository
{
    public function save(Auth0Client $auth0Client): void
    {
        Auth0ClientModel::query()->updateOrCreate(
            [
                'auth0_client_id' => $auth0Client->clientId,
                'auth0_tenant' => $auth0Client->tenant->value,
            ],
            [
                'integration_id' => $auth0Client->integrationId->toString(),
                'auth0_client_id' => $auth0Client->clientId,
                'auth0_client_secret' => $auth0Client->clientSecret,
                'auth0_tenant' => $auth0Client->tenant->value,
            ]
        );
    }

    public function getByIntegrationId(UuidInterface $integrationId): Auth0ClientsForIntegration
    {
        $auth0Clients = Auth0ClientModel::query()
            ->where('integration_id', $integrationId->toString())
            ->get()
            ->map(static fn (Auth0ClientModel $auth0ClientModel) => $auth0ClientModel->toDomain())
            ->toArray();

        return new Auth0ClientsForIntegration(...$auth0Clients);
    }
}
