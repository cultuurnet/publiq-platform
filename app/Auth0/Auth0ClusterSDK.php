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

    public function createClientsForIntegration(Integration $integration): Auth0ClientsForIntegration
    {
        return new Auth0ClientsForIntegration(
            ...array_map(
                static fn (Auth0TenantSDK $sdk) => $sdk->createClientForIntegration($integration),
                $this->auth0TenantSDKs
            )
        );
    }
}
