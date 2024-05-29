<?php

declare(strict_types=1);

namespace App\Keycloak\Repositories;

use App\Keycloak\Client;
use App\Keycloak\Models\KeycloakClientModel;
use App\Keycloak\RealmCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentKeycloakClientRepository implements KeycloakClientRepository
{
    public function create(Client ...$clients): void
    {
        if (count($clients) === 0) {
            return;
        }

        DB::transaction(static function () use ($clients) {
            foreach ($clients as $client) {
                KeycloakClientModel::query()
                    ->create(
                        [
                            'id' => $client->id->toString(),
                            'integration_id' => $client->integrationId->toString(),
                            'client_id' => $client->clientId->toString(),
                            'client_secret' => $client->clientSecret,
                            'realm' => $client->realm->publicName,
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
            static fn ($integrationId) => $integrationId->toString(),
            $integrationIds
        );

        return KeycloakClientModel::query()
            ->whereIn('integration_id', $ids)
            ->orderBy('created_at')
            ->get()
            ->map(static fn (KeycloakClientModel $model) => $model->toDomain())
            ->toArray();
    }

    public function getMissingRealmsByIntegrationId(UuidInterface $integrationId, RealmCollection $realms): RealmCollection
    {
        $clients = $this->getByIntegrationId($integrationId);

        if (count($clients) === $realms->count()) {
            return new RealmCollection();
        }

        $existingRealms = array_map(
            static fn (Client $client) => $client->realm,
            $clients
        );

        return new RealmCollection(array_udiff(
            $realms->toArray(),
            $existingRealms,
            fn (Client $t1, Client $t2) => strcmp($t1->id->toString(), $t2->id->toString())
        ));
    }
}
