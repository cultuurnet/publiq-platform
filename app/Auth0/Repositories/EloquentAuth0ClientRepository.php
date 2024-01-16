<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentAuth0ClientRepository implements Auth0ClientRepository
{
    public function save(Auth0Client ...$auth0Clients): void
    {
        if (count($auth0Clients) === 0) {
            return;
        }

        DB::transaction(static function () use ($auth0Clients) {
            foreach ($auth0Clients as $auth0Client) {
                $tenant = $auth0Client->tenant->value;
                Auth0ClientModel::query()
                    ->updateOrCreate(
                        [
                            'auth0_client_id' => $auth0Client->clientId,
                            'auth0_tenant' => $tenant,
                        ],
                        [
                            'id' => $auth0Client->id->toString(),
                            'integration_id' => $auth0Client->integrationId->toString(),
                            'auth0_client_id' => $auth0Client->clientId,
                            'auth0_client_secret' => $auth0Client->clientSecret,
                            'auth0_tenant' => $tenant,
                            'distributed_at' => ($tenant === Auth0Tenant::Production->value) ? null : new DateTime(),
                        ]
                    );
            }
        });
    }

    public function distribute(Auth0Client ...$auth0Clients): void
    {
        $ids = array_map(
            fn (Auth0Client $client) => $client->id->toString(),
            $auth0Clients
        );

        Auth0ClientModel::query()
            ->whereIn('id', $ids)
            ->touch('distributed_at');
    }

    public function getByIntegrationId(UuidInterface $integrationId): array
    {
        return Auth0ClientModel::query()
            ->where('integration_id', $integrationId->toString())
            ->get()
            ->map(static fn (Auth0ClientModel $auth0ClientModel) => $auth0ClientModel->toDomain())
            ->toArray();
    }

    /**
     * @throws ModelNotFoundException<Model>
     */
    public function getById(UuidInterface $id): Auth0Client
    {
        return Auth0ClientModel::query()
            ->where('id', $id->toString())
            ->firstOrFail()
            ->toDomain();
    }

    /**
     * @inheritDoc
     */
    public function getByIntegrationIds(array $integrationIds): array
    {
        $ids = array_map(
            fn ($integrationId) => $integrationId->toString(),
            $integrationIds
        );

        return Auth0ClientModel::query()
            ->whereIn('integration_id', $ids)
            ->orderBy('created_at')
            ->get()
            ->map(static fn (Auth0ClientModel $model) => $model->toDomain())
            ->toArray();
    }
}
