<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards\Auth0;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0ClusterSDK;
use App\Nova\ActionGuards\ActionGuard;

final readonly class BlockAuth0ClientGuard implements ActionGuard
{
    public function __construct(
        private Auth0ClusterSDK $clusterSDK,
    ) {
    }

    public function canDo(object $auth0Client): bool
    {
        if (!$auth0Client instanceof Auth0Client) {
            return false;
        }

        $grants = $this->clusterSDK->findGrantsOnClient($auth0Client);
        return ! empty($grants);
    }
}
