<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Keycloak\Client;
use App\Keycloak\Realm;
use Ramsey\Uuid\UuidInterface;

interface ApiClientInterface
{
    public function createClient(Realm $realm, Integration $integration): UuidInterface;

    public function addScopeToClient(Realm $realm, UuidInterface $clientId, UuidInterface $scopeId): void;

    public function fetchClient(Realm $realm, Integration $integration): Client;
}
