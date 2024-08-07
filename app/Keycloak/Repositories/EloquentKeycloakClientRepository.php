<?php

declare(strict_types=1);

namespace App\Keycloak\Repositories;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Environments;
use App\Keycloak\Client;
use App\Keycloak\Models\KeycloakClientModel;
use App\Keycloak\Realms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentKeycloakClientRepository implements KeycloakClientRepository
{
    public function __construct(private readonly Realms $realms)
    {
    }

    public function create(Client ...$clients): void
    {
        if (count($clients) === 0) {
            return;
        }

        DB::transaction(function () use ($clients) {
            foreach ($clients as $client) {
                KeycloakClientModel::query()
                    ->create(
                        [
                            'id' => $client->id->toString(),
                            'integration_id' => $client->integrationId->toString(),
                            'client_id' => $client->clientId,
                            'client_secret' => $client->clientSecret,
                            'realm' => $this->realms->getRealmByEnvironment($client->environment)->publicName,
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

    public function getMissingEnvironmentsByIntegrationId(UuidInterface $integrationId): Environments
    {
        $clients = $this->getByIntegrationId($integrationId);

        $environments = Environment::cases();

        if (count($clients) === count($environments)) {
            return new Environments();
        }

        $existingEnvironments = array_map(
            static fn (Client $client) => $client->environment,
            $clients
        );

        return new Environments(array_udiff(
            $environments,
            $existingEnvironments,
            static fn (Environment $t1, Environment $t2) => strcmp($t1->value, $t2->value)
        ));
    }
}
