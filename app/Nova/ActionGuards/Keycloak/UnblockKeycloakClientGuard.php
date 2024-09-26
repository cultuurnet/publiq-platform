<?php

declare(strict_types=1);

namespace App\Nova\ActionGuards\Keycloak;

use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Nova\ActionGuards\ActionGuard;
use App\Keycloak\Client;

final readonly class UnblockKeycloakClientGuard implements ActionGuard
{
    public function __construct(
        private CachedKeycloakClientStatus $cachedKeycloakClientStatus,
    ) {
    }


    public function canDo(object $client): bool
    {
        if (!$client instanceof Client) {
            return false;
        }

        try {
            return $this->cachedKeycloakClientStatus->isClientBlocked($client);
        }
        catch(KeyCloakApiFailed) {
            return false;
        }
    }
}
