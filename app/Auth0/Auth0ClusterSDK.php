<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Domain\Integrations\Integration;

final class Auth0ClusterSDK
{
    /**
     * @var Auth0TenantSDK[]
     */
    private array $auth0TenantSDKs = [];

    public function __construct(Auth0TenantSDK ...$auth0TenantSDKs)
    {
        foreach ($auth0TenantSDKs as $auth0TenantSDK) {
            $this->auth0TenantSDKs[$auth0TenantSDK->auth0Tenant->value] = $auth0TenantSDK;
        }
    }

    public function getTenantSDK(Auth0Tenant $auth0Tenant): Auth0TenantSDK
    {
        return $this->auth0TenantSDKs[$auth0Tenant->value];
    }

    /**
     * @return Auth0Client[]
     */
    public function createClientsForIntegration(Integration $integration): array
    {
        return array_values(
            array_map(
                static fn (Auth0TenantSDK $sdk) => $sdk->createClientForIntegration($integration),
                $this->auth0TenantSDKs
            )
        );
    }

    public function updateClientsForIntegration(Integration $integration, Auth0Client ...$auth0Clients): void
    {
        foreach ($auth0Clients as $auth0Client) {
            $this->auth0TenantSDKs[$auth0Client->tenant->value]->updateClient($integration, $auth0Client);
        }
    }

    /**
     * @throws Auth0TenantNotConfigured
     */
    public function createClientForIntegrationOnAuth0Tenant(
        Integration $integration,
        Auth0Tenant $auth0Tenant
    ): Auth0Client {
        if (!array_key_exists($auth0Tenant->value, $this->auth0TenantSDKs)) {
            throw new Auth0TenantNotConfigured($auth0Tenant);
        }

        return $this->auth0TenantSDKs[$auth0Tenant->value]->createClientForIntegration($integration);
    }

    public function blockClients(Auth0Client ...$auth0Clients): void
    {
        foreach ($auth0Clients as $auth0Client) {
            $this->auth0TenantSDKs[$auth0Client->tenant->value]->blockClient($auth0Client);
        }
    }

    public function unblockClients(Auth0Client ...$auth0Clients): void
    {
        foreach ($auth0Clients as $auth0Client) {
            $this->auth0TenantSDKs[$auth0Client->tenant->value]->unblockClient($auth0Client);
        }
    }

    /** @return string[] */
    public function findGrantsOnClient(Auth0Client $auth0Client): array
    {
        return $this->auth0TenantSDKs[$auth0Client->tenant->value]->findGrantsOnClient($auth0Client);
    }
}
