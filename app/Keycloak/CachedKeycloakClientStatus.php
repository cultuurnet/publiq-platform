<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Keycloak\Client\ApiClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Exception\RealmNotAvailable;
use Psr\Log\LoggerInterface;

final class CachedKeycloakClientStatus
{
    private array $statuses = [];

    public function __construct(private readonly ApiClient $apiClient, private readonly LoggerInterface $logger)
    {
    }

    /** @throws KeyCloakApiFailed|RealmNotAvailable */
    public function isClientBlocked(Client $client): bool
    {
        $uuid = $client->id->toString();

        if (! isset($this->statuses[$uuid])) {
            $this->statuses[$uuid] = $this->apiClient->fetchIsClientActive($client);
        } else {
            $this->logger->info(self::class . '  - ' . $uuid . ': cache hit: ' . ($this->statuses[$uuid] ? 'Active' : 'Blocked'));
        }

        return ! $this->statuses[$uuid];
    }
}
