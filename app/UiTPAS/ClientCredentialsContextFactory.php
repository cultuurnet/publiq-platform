<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Keycloak\KeycloakConfig;

final class ClientCredentialsContextFactory
{
    public static function getUitIdTestContext(): ClientCredentialsContext
    {
        return new ClientCredentialsContext(
            Environment::Testing,
            config(KeycloakConfig::TEST_BASE_URL),
            config(KeycloakConfig::TEST_CLIENT_ID),
            config(KeycloakConfig::TEST_CLIENT_SECRET),
        );
    }

    public static function getUitIdProdContext(): ClientCredentialsContext
    {
        return new ClientCredentialsContext(
            Environment::Production,
            config(KeycloakConfig::KEYCLOAK_DOMAIN),
            config(KeycloakConfig::KEYCLOAK_CLIENT_ID),
            config(KeycloakConfig::KEYCLOAK_CLIENT_SECRET),
        );
    }
}
