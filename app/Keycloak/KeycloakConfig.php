<?php

declare(strict_types=1);

namespace App\Keycloak;

final class KeycloakConfig
{
    public const KEYCLOAK_CREATION_ENABLED = 'keycloak.creationEnabled';
    public const KEYCLOAK_DOMAIN = 'keycloak.login.domain';
    public const KEYCLOAK_CLIENT_ID = 'keycloak.login.clientId';
    public const KEYCLOAK_CLIENT_SECRET = 'keycloak.login.clientSecret';
    public const KEYCLOAK_REALM_NAME = 'keycloak.login.realmName';
    public const KEYCLOAK_LOGIN_PARAMETERS = 'keycloak.login.parameters';
    public const KEYCLOAK_ENFORCE_2FA_FOR_ADMINS = 'keycloak.login.enforce2FAForAdmins';

    // We need the Test env separately, because the UiTPAS integrations add new clients to a fixed test organizer
    public const TEST_INTERNAL_NAME = 'keycloak.uitid_realms.test.internalName';
    public const TEST_BASE_URL = 'keycloak.uitid_realms.test.base_url';
    public const TEST_CLIENT_ID = 'keycloak.uitid_realms.test.client_id';
    public const TEST_CLIENT_SECRET = 'keycloak.uitid_realms.test.client_secret';
}
