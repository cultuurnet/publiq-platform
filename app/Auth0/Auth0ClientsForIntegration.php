<?php

declare(strict_types=1);

namespace App\Auth0;

use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

final class Auth0ClientsForIntegration
{
    public readonly UuidInterface $integrationId;
    private array $auth0ClientPerTenant;

    public function __construct(
        Auth0Client ...$auth0Clients
    ) {
        $integrationIds = [];
        foreach ($auth0Clients as $auth0Client) {
            $integrationIds[] = $auth0Client->integrationId->toString();
            $this->auth0ClientPerTenant[$auth0Client->tenant->value] = $auth0Client;
        }

        $integrationIds = array_unique($integrationIds);
        if (count($integrationIds) !== 1) {
            throw new InvalidArgumentException('Cannot combine clients for multiple integrations in one Auth0ClientsForIntegration object. (Integration ids: ' . implode(', ', $integrationIds) . ')');
        }
        $this->integrationId = $integrationIds[0];
    }

    public function getClientForTenant(Auth0Tenant $tenant): ?Auth0Client
    {
        return $this->auth0ClientPerTenant[$tenant->value] ?? null;
    }

    /**
     * @return Auth0Client[]
     */
    public function getAllClients(): array
    {
        return array_values($this->auth0ClientPerTenant);
    }
}
