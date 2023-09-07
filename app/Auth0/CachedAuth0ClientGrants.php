<?php

declare(strict_types=1);

namespace App\Auth0;

final class CachedAuth0ClientGrants
{
    private array $grants = [];

    public function __construct(private readonly Auth0ClusterSDK $sdk)
    {
    }

    public function findGrantsOnClient(Auth0Client $auth0Client): array
    {
        if(! isset($this->grants[$auth0Client->clientId])) {
            $this->grants[$auth0Client->clientId] = $this->sdk->findGrantsOnClient($auth0Client);
        }

        return $this->grants[$auth0Client->clientId];
    }
}
