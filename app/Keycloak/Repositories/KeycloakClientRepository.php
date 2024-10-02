<?php

declare(strict_types=1);

namespace App\Keycloak\Repositories;

use App\Domain\Integrations\Environments;
use App\Keycloak\Client;
use App\Keycloak\Exception\RealmNotAvailable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\UuidInterface;

interface KeycloakClientRepository
{
    /**
     * @throws RealmNotAvailable
     */
    public function create(Client ...$clients): void;

    /**
     * @return Client[]
     */
    public function getByIntegrationId(UuidInterface $integrationId): array;

    /**
     * @throws ModelNotFoundException<Model>
     */
    public function getById(UuidInterface $id): Client;

    /**
     * @param array<UuidInterface> $integrationIds
     * @return Client[]
     */
    public function getByIntegrationIds(array $integrationIds): array;

    public function getMissingEnvironmentsByIntegrationId(UuidInterface $integrationId): Environments;
}
