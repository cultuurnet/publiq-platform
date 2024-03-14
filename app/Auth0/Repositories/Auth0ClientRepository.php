<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

use App\Auth0\Auth0Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\UuidInterface;

interface Auth0ClientRepository
{
    public function save(Auth0Client ...$auth0Clients): void;

    public function distribute(Auth0Client ...$auth0Clients): void;

    /**
     * @return Auth0Client[]
     */
    public function getByIntegrationId(UuidInterface $integrationId): array;

    /**
     * @return Auth0Client[]
     */
    public function getDistributedByIntegrationId(UuidInterface $integrationId): array;

    /**
     * @throws ModelNotFoundException<Model>
     */
    public function getById(UuidInterface $id): Auth0Client;

    /**
     * @param array<UuidInterface> $integrationIds
     * @return Auth0Client[]
     */
    public function getByIntegrationIds(array $integrationIds): array;
}
