<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\Realm;
use Ramsey\Uuid\UuidInterface;

interface ApiClient
{
    public function createClient(Realm $realm, Integration $integration): UuidInterface;

    public function addScopeToClient(Realm $realm, UuidInterface $clientId, UuidInterface $scopeId): void;

    public function fetchClient(Realm $realm, Integration $integration): Client;

    public function fetchIsClientEnabled(Realm $realm, UuidInterface $integrationId): bool;

    public function enableClient(Client $client): void;

    public function disableClient(Client $client): void;

    public function updateClient(Client $client, array $body): void;

    public function resetScopes(Client $client): void;
}
