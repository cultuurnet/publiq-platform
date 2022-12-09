<?php

declare(strict_types=1);

namespace App\Auth0;

use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

final class Auth0ClientsForIntegration
{
    private array $auth0ClientPerTenant;

    public function __construct(
        public readonly UuidInterface $integrationId,
        Auth0Client ...$auth0Clients
    ) {
        foreach ($auth0Clients as $auth0Client) {
            if (!$auth0Client->integrationId->equals($this->integrationId)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Client with id %s of integration id %s does not belong to expected integration id %s',
                        $auth0Client->clientId,
                        $auth0Client->integrationId->toString(),
                        $this->integrationId->toString()
                    )
                );
            }
            $this->auth0ClientPerTenant[$auth0Client->tenant->value] = $auth0Client;
        }
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
