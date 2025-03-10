<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\ClientId\ClientIdFactory;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Exception\RealmNotAvailable;
use App\Keycloak\Realm;
use Ramsey\Uuid\UuidInterface;

interface ApiClient
{
    public function createClient(Realm $realm, Integration $integration, ClientIdFactory $clientIdFactory): Client;

    public function addScopeToClient(Client $client, UuidInterface $scopeId): void;

    /** @throws KeyCloakApiFailed|RealmNotAvailable */
    public function fetchIsClientActive(Client $client): bool;

    public function unblockClient(Client $client): void;

    public function blockClient(Client $client): void;

    public function updateClient(Client $client, array $body): void;

    public function deleteScopes(Client $client): void;
}
