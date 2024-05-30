<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards\Auth0;

use App\Auth0\Auth0Client;
use App\Auth0\CachedAuth0ClientGrants;
use App\Nova\ActionGuards\ActionGuard;

final readonly class UnblockAuth0ClientGuard implements ActionGuard
{
    public function __construct(
        private CachedAuth0ClientGrants $cachedAuth0ClientGrants,
    ) {
    }

    public function canDo(object $auth0Client): bool
    {
        if (!$auth0Client instanceof Auth0Client) {
            return false;
        }

        $grants = $this->cachedAuth0ClientGrants->findGrantsOnClient($auth0Client);
        return empty($grants);
    }
}
