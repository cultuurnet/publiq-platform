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
            config(KeycloakConfig::UITID_TEST_BASE_URL),
            config(KeycloakConfig::UITID_CLIENT_ID),
            config(KeycloakConfig::UITID_CLIENT_SECRET),
            config(KeycloakConfig::UITID_TEST_INTERNAL_NAME),
        );
    }

    public static function getUitIdProdContext(): ClientCredentialsContext
    {
        return new ClientCredentialsContext(
            Environment::Production,
            config(KeycloakConfig::KEYCLOAK_DOMAIN),
            config(KeycloakConfig::KEYCLOAK_CLIENT_ID),
            config(KeycloakConfig::KEYCLOAK_CLIENT_SECRET),
            config(KeycloakConfig::KEYCLOAK_REALM_NAME),
        );
    }
}
