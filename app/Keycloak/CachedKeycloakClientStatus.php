<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Keycloak\Client\ApiClient;
use Psr\Log\LoggerInterface;

final class CachedKeycloakClientStatus
{
    private array $statuses = [];

    public function __construct(private readonly ApiClient $apiClient, private readonly LoggerInterface $logger)
    {
    }

    public function isClientEnabled(Client $client): bool
    {
        $uuid = $client->id->toString();

        if(! isset($this->statuses[$uuid])) {
            $this->statuses[$uuid] = $this->apiClient->fetchIsClientEnabled($client->realm, $client->integrationId);
        } else {
            $this->logger->info(self::class . '  - ' . $uuid . ': cache hit: ' . ($this->statuses[$uuid] ? 'Enabled' : 'Disabled'));
        }

        return $this->statuses[$uuid];
    }
}
