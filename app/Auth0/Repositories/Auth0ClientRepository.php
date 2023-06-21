<?php

declare(strict_types=1);

namespace App\Auth0\Repositories;

use App\Auth0\Auth0Client;
use Ramsey\Uuid\UuidInterface;

interface Auth0ClientRepository
{
    public function save(Auth0Client ...$auth0Clients): void;

    /**
     * @return Auth0Client[]
     */
    public function getByIntegrationId(UuidInterface $integrationId): array;

    /**
     * @param array<UuidInterface> $integrationIds
     * @return Auth0Client[]
     */
    public function getByIntegrationIds(array $integrationIds): array;
}
