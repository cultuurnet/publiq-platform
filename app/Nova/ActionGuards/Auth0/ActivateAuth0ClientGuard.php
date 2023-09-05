<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards\Auth0;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0ClusterSDK;
use App\Nova\ActionGuards\ActionGuardInterface;

final class ActivateAuth0ClientGuard implements ActionGuardInterface
{
    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
    ) {
    }

    public function canDo(object $auth0Client): bool
    {
        if (!$auth0Client instanceof Auth0Client) {
            return false;
        }

        $grants = $this->clusterSDK->findGrantsOnClient($auth0Client);
        return empty($grants);
    }
}
