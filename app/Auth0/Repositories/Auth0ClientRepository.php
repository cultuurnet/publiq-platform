<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

use App\Auth0\Auth0ClientsForIntegration;
use App\Auth0\Models\Auth0ClientModel;
use Ramsey\Uuid\UuidInterface;

final class Auth0ClientRepository
{
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
