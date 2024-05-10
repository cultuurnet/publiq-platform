<?php

declare(strict_types=1);

namespace App\Keycloak\Repositories;

use App\Keycloak\Client;
use App\Keycloak\Models\KeycloakClientModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentKeycloakClientRepository implements KeycloakClientRepository
{
    public function save(Client ...$clients): void
    {
        if (count($clients) === 0) {
            return;
        }

        DB::transaction(static function () use ($clients) {
            foreach ($clients as $client) {
                KeycloakClientModel::query()
                    ->updateOrCreate(
                        [
                            'client_id' => $client->clientId,
                            'realm' => $client->realm->internalName,
                        ],
                        [
                            'id' => $client->id->toString(),
                            'integration_id' => $client->integrationId->toString(),
                            'client_id' => $client->clientId,
                            'client_secret' => $client->clientSecret,
                            'realm' => $client->realm->internalName,
                        ]
                    );
            }
        });
    }

    public function getByIntegrationId(UuidInterface $integrationId): array
    {
        return KeycloakClientModel::query()
            ->where('integration_id', $integrationId->toString())
            ->get()
            ->map(static fn (KeycloakClientModel $KeycloakClientModel) => $KeycloakClientModel->toDomain())
            ->toArray();
    }

    /**
     * @throws ModelNotFoundException<Model>
     */
    public function getById(UuidInterface $id): Client
    {
        return KeycloakClientModel::query()
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

        return KeycloakClientModel::query()
            ->whereIn('integration_id', $ids)
            ->orderBy('created_at')
            ->get()
            ->map(static fn (KeycloakClientModel $model) => $model->toDomain())
            ->toArray();
    }
}
