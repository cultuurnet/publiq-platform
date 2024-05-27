<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\Realm;
use Ramsey\Uuid\UuidInterface;

interface ApiClient
{
    public function createClient(Realm $realm, Integration $integration): void;

    public function addScopeToClient(Realm $realm, UuidInterface $clientId, UuidInterface $scopeId): void;

    public function fetchClient(Realm $realm, Integration $integration): Client;

    public function fetchIsClientActive(Realm $realm, UuidInterface $integrationId): bool;

    public function unblockClient(Client $client): void;

    public function blockClient(Client $client): void;

    public function updateClient(Client $client, array $body): void;

    public function deleteScopes(Client $client): void;
}
