<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;

final class ClientCredentialsContextFactory
{
    public static function getUitIdTestContext(): ClientCredentialsContext
    {
        return new ClientCredentialsContext(
            Environment::Testing,
            config(UiTPASConfig::TEST_OAUTH_TOKEN_URL->value),
            config(UiTPASConfig::TEST_CLIENT_ID->value),
            config(UiTPASConfig::TEST_CLIENT_SECRET->value),
            UiTPASConfig::REALM_NAME->value,
        );
    }

    public static function getUitIdProdContext(): ClientCredentialsContext
    {
        return new ClientCredentialsContext(
            Environment::Production,
            config(UiTPASConfig::PROD_OAUTH_TOKEN_URL->value),
            config(UiTPASConfig::PROD_CLIENT_ID->value),
            config(UiTPASConfig::PROD_CLIENT_SECRET->value),
            UiTPASConfig::REALM_NAME->value,
        );
    }
}
