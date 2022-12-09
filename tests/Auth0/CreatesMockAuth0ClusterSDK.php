<?php

declare(strict_types=1);

namespace Tests\Auth0;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Auth0Tenant;
use App\Auth0\Auth0TenantSDK;
use Auth0\SDK\Configuration\SdkConfiguration;
use Psr\Http\Client\ClientInterface;

trait CreatesMockAuth0ClusterSDK
{
    public function createMockAuth0ClusterSDK(ClientInterface $httpClient): Auth0ClusterSDK
    {
        return new Auth0ClusterSDK(
            new Auth0TenantSDK(
                Auth0Tenant::Acceptance,
                new SdkConfiguration(
                    strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                    domain: 'mock-acc.auth0.com',
                    audience: ['https://mock.auth0.com/api/v2/'],
                    httpClient: $httpClient,
                    managementToken: 'mock-token',
                )
            ),
            new Auth0TenantSDK(
                Auth0Tenant::Testing,
                new SdkConfiguration(
                    strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                    domain: 'mock-test.auth0.com',
                    audience: ['https://mock.auth0.com/api/v2/'],
                    httpClient: $httpClient,
                    managementToken: 'mock-token',
                )
            ),
            new Auth0TenantSDK(
                Auth0Tenant::Production,
                new SdkConfiguration(
                    strategy: SdkConfiguration::STRATEGY_MANAGEMENT_API,
                    domain: 'mock-prod.auth0.com',
                    audience: ['https://mock.auth0.com/api/v2/'],
                    httpClient: $httpClient,
                    managementToken: 'mock-token',
                )
            )
        );
    }
}
